<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\BackgroundJobHelper;
use Illuminate\Support\Facades\Log;

class RunBackgroundJob extends Command
{
    protected $signature = 'job:run {class} {method} {params?} {priority=0} {max_retries=3} {retry_delay=5} {timeout=60}';
    protected $description = 'Run background jobs securely with validation and sanitization';

    public function handle()
    {
        Log::info($this->argument('class'));

        $class = $this->argument('class');
        $method = $this->argument('method');
        $params = json_decode($this->argument('params') ?: '[]', true);
        $priority = $this->argument('priority');
        $maxRetries = $this->argument('max_retries');
        $retryDelay = $this->argument('retry_delay');
        $timeout = $this->argument('timeout');

        try {
            BackgroundJobHelper::runBackgroundJob($class, $method, $params, $maxRetries, $priority, $retryDelay, $timeout);
            $this->info("Job executed securely: {$class}::{$method}");
        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
        }
    }
}
