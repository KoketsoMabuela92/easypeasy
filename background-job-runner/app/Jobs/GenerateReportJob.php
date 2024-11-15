<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Log;

class GenerateReportJob
{
    public function handle()
    {
        sleep(3); // Simulate long-running process
        Log::info('Report generated successfully!');
    }
}

