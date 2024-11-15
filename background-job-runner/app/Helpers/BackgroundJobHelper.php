<?php

namespace App\Helpers;

use App\Jobs\GenerateReportJob;
use App\Models\JobLog;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Log;

class BackgroundJobHelper
{
    // List of approved job classes and their allowed methods
    protected static $approvedClasses = [
        GenerateReportJob::class => ['handle'],
    ];

    /**
     * Run a background job securely.
     * @throws Exception
     */
    public static function runBackgroundJob(
        string $jobClass,
        string $method,
        array $params = [],
        int $maxRetries = 3,
        int $priority = 0,
        int $retryDelay = 5,
        int $timeout = 60
    ): bool {
        // 1. Validate the job class
        if (!array_key_exists($jobClass, self::$approvedClasses)) {
            Log::error("Unauthorized job class: {$jobClass}");
            throw new Exception("Unauthorized job class: {$jobClass}");
        }

        // 2. Validate the method
        if (!in_array($method, self::$approvedClasses[$jobClass])) {
            Log::error("Unauthorized method: {$jobClass}::{$method}");
            throw new Exception("Unauthorized method: {$jobClass}::{$method}");
        }

        // 3. Sanitize the job class and method
        $jobClass = self::sanitizeClassName($jobClass);
        $method = self::sanitizeMethodName($method);

        // 4. Proceed with the job execution if validated
        return self::executeJob($jobClass, $method, $params, $maxRetries, $priority, $retryDelay, $timeout);
    }

    /**
     * Sanitize the class name to avoid malicious input.
     * @throws Exception
     */
    private static function sanitizeClassName(string $jobClass): string
    {
        if (preg_match('/[^a-zA-Z0-9\\\\_]/', $jobClass)) {
            throw new Exception("Invalid class name: {$jobClass}");
        }

        return $jobClass;
    }

    /**
     * Sanitize the method name to avoid malicious input.
     * @throws Exception
     */
    private static function sanitizeMethodName(string $method): string
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $method)) {
            throw new Exception("Invalid method name: {$method}");
        }

        return $method;
    }

    /**
     * Execute the job.
     * @throws BindingResolutionException
     */
    private static function executeJob(
        string $jobClass,
        string $method,
        array $params = [],
        int $maxRetries = 3,
        int $priority = 0,
        int $retryDelay = 5,
        int $timeout = 60
    ): bool
    {
        // Create a job log entry
        $jobLog = JobLog::create([
            'job_name' => $jobClass,
            'method_name' => $method,
            'status' => 'running',
            'retry_count' => 0,
            'priority' => $priority,
            'max_retries' => $maxRetries,
            'retry_delay' => $retryDelay,
            'timeout' => $timeout,
        ]);

        // Execute the job with retries
        for ($retryCount = 0; $retryCount <= $maxRetries; $retryCount++) {
            try {
                $job = app()->make($jobClass);
                call_user_func_array([$job, $method], $params);
                $jobLog->update(['status' => 'completed']);

                return true; // Exit the method if job succeeds
            } catch (Exception $e) {
                self::logError("Job failed: {$jobClass}::{$method} - Attempt {$retryCount} - Error: {$e->getMessage()}");

                if ($retryCount === $maxRetries) {
                    $jobLog->update([
                        'status' => 'failed',
                        'error_message' => $e->getMessage(),
                    ]);
                    throw $e; // Stop retries and propagate the error
                }

                sleep($retryDelay); // Wait before retrying
            }
        }

        return !($jobLog->status === 'failed');
    }

    /**
     * Log errors to background_jobs_errors.log.
     */
    private static function logError(string $message): void
    {
        $logFile = storage_path('logs/background_jobs_errors.log');
        $formattedMessage = "[" . now() . "] ERROR: {$message}\n";
        file_put_contents($logFile, $formattedMessage, FILE_APPEND);
    }
}
