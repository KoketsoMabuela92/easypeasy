<?php

namespace App\Http\Controllers;

use App\Models\JobLog;

class JobController extends Controller
{
    public function index()
    {
        $jobLogs = JobLog::orderByDesc('priority')->paginate(10);
        return view('job_dashboard', compact('jobLogs'));
    }

    public function cancelJob($id)
    {
        $jobLog = JobLog::findOrFail($id);
        $jobLog->update(['status' => 'cancelled']);
        return redirect()->route('dashboard');
    }
}
