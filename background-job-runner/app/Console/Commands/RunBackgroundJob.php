<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\BackgroundJobHelper;
use Illuminate\Support\Facades\Log;
use App\Models\JobLog;
use Illuminate\Support\Facades\Queue;

class RunBackgroundJob extends Command
{
    protected $signature = 'job:run {class} {method} {params?} {priority=0} {max_retries=3} {retry_delay=5} {timeout=60}';
    protected $description = 'Run background jobs securely with validation and sanitization';

    public function handle()
    {
        // Extract arguments
        $class = $this->argument('class');
        $method = $this->argument('method');
        $params = $this->argument('params') ?: '[]';
        $priority = $this->argument('priority');
        $maxRetries = $this->argument('max_retries');
        $retryDelay = $this->argument('retry_delay');
        $timeout = $this->argument('timeout');

        // Validate and decode params
        $paramsArray = json_decode($params, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error("Invalid JSON for params: {$params}");
            return;
        }

        // Validate class and method
        if (!class_exists($class) || !method_exists($class, $method)) {
            $this->error("Invalid class or method: {$class}::{$method}");
            return;
        }

        // Create job log
        try {
            $jobLog = JobLog::create([
                'job_name' => $class,
                'method_name' => $method,
                'status' => 'running',
                'priority' => $priority,
                'retry_count' => 0,
                'max_retries' => $maxRetries,
                'retry_delay' => $retryDelay,
                'timeout' => $timeout,
            ]);
            Log::info("JobLog created: ID {$jobLog->id}");
        } catch (\Exception $e) {
            $this->error("Failed to create job log: {$e->getMessage()}");
            return;
        }

        // If you want to use queues for job execution, dispatch the job to the queue
        try {
            // Push the job to the queue (adjust for priority)
            Queue::pushOn('default', function () use ($class, $method, $paramsArray, $maxRetries, $priority, $retryDelay, $timeout, $jobLog) {
                BackgroundJobHelper::runBackgroundJob(
                    $class,
                    $method,
                    $paramsArray,
                    $maxRetries,
                    $retryDelay,
                    $timeout,
                    $jobLog->id
                );
            });

            $this->info("Job dispatched to queue: {$class}::{$method}");
        } catch (\Exception $e) {
            $jobLog->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
            $this->error("Failed to dispatch job to the queue: {$e->getMessage()}");
        }
    }
}
