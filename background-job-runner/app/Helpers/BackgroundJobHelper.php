<?php
namespace App\Helpers;

use App\Models\JobLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class BackgroundJobHelper
{
    /**
     * Run the background job securely with cancellation check, retry mechanism, and priority handling.
     *
     * @param string $class
     * @param string $method
     * @param array $params
     * @param int $maxRetries
     * @param int $retryDelay
     * @param int $timeout
     * @param int $jobLogId
     * @return void
     * @throws \Exception
     */
    public static function runBackgroundJob($class, $method, $params, $maxRetries, $retryDelay, $timeout, $jobLogId)
    {
        // Validate job log ID
        Log::info("Validating job log ID: {$jobLogId}");

        if (empty($jobLogId) || !is_numeric($jobLogId)) {
            self::logError("Invalid job log ID: {$jobLogId}");
            throw new \Exception("Invalid job log ID.");
        }

        // Fetch the job log
        $jobLog = JobLog::find($jobLogId);
        if (!$jobLog) {
            self::logError("Job log not found for ID: {$jobLogId}");
            throw new \Exception("Job log not found.");
        }

        Log::info("Successfully retrieved JobLog with ID: {$jobLogId}");

        $retries = 0;

        // Log job start
        self::logStatus($jobLogId, 'running', "Starting job: {$class}::{$method}");

        // Implement priority handling
        $priority = $jobLog->priority; // Assume 'priority' is a column in your JobLog table
        Log::info("Job priority: {$priority}. Higher priority jobs will execute first.");

        while ($retries <= $maxRetries) {
            try {
                // Check for cancellation
                $jobLog->refresh();
                if ($jobLog->status === 'cancelled') {
                    self::logStatus($jobLogId, 'cancelled', 'Job was cancelled.');
                    return;
                }

                // Execute the job
                $jobInstance = app($class);
                call_user_func_array([$jobInstance, $method], $params);

                // Mark as completed
                $jobLog->update(['status' => 'completed']);
                self::logStatus($jobLogId, 'completed', "Job successfully completed.");
                return;
            } catch (\Exception $e) {
                // Log the error for the attempt
                self::logError("Job (ID: {$jobLogId}) failed on attempt " . ($retries + 1) . ": {$e->getMessage()}");

                $retries++;
                if ($retries <= $maxRetries) {
                    self::logStatus($jobLogId, 'retrying', "Retrying job: attempt {$retries}/{$maxRetries}");
                    sleep($retryDelay);
                }
            }
        }

        // If max retries exceeded, mark as failed
        $jobLog->update(['status' => 'failed']);
        self::logStatus($jobLogId, 'failed', "Job failed after {$maxRetries} attempts.");
    }

    /**
     * Log job status with timestamps.
     *
     * @param int $jobLogId
     * @param string $status
     * @param string $message
     * @return void
     */
    private static function logStatus(int $jobLogId, string $status, string $message)
    {
        $timestamp = now()->format('Y-m-d H:i:s');
        Log::channel('background_jobs_status')->info("[{$timestamp}] Job ID: {$jobLogId}, Status: {$status}, Message: {$message}");
    }

    /**
     * Log errors to a separate error file.
     *
     * @param string $message
     * @return void
     */
    private static function logError(string $message)
    {
        $timestamp = now()->format('Y-m-d H:i:s');
        Log::channel('background_jobs_errors')->error("[{$timestamp}] {$message}");
    }

    /**
     * Queue jobs with priority using Laravel's Queue system.
     *
     * @return void
     */
    public static function queueJobsWithPriority()
    {
        $jobs = JobLog::where('status', 'pending')
            ->orderBy('priority', 'asc') // Ascending for high-priority jobs first
            ->get();

        foreach ($jobs as $job) {
            // Dispatch the job to the queue
            Queue::pushOn('default', function () use ($job) {
                self::runBackgroundJob(
                    $job->job_name,
                    $job->method_name,
                    json_decode($job->params, true),
                    $job->max_retries,
                    $job->retry_delay,
                    $job->timeout,
                    $job->id
                );
            });
        }
    }
}

