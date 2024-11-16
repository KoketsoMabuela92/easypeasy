<?php

namespace App\Http\Controllers;

use App\Models\JobLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class JobController extends Controller
{
    /**
     * Display a listing of the jobs.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        // Get filter options from query parameters
        $status = $request->query('status', 'all');
        $priority = $request->query('priority', null);

        // Filter jobs by status
        $query = JobLog::query();

        if ($status != 'all') {
            $query->where('status', $status);
        }

        if ($priority !== null) {
            $query->where('priority', $priority);
        }

        // Paginate jobs and pass them to the view
        $jobLogs = $query->orderByDesc('priority')->paginate(10);

        return view('job_dashboard', compact('jobLogs'));
    }

    /**
     * Cancel a job from the queue.
     *
     * @param  int  $id
     * @return RedirectResponse
     */
    public function cancelJob($id)
    {
        $jobLog = JobLog::findOrFail($id);

        if (!$jobLog) {
            return redirect()->route('dashboard.index')->with('error', 'Job not found.');
        }

        // Mark the job as cancelled
        $jobLog->update(['status' => 'cancelled']);

        // Redirect back to the dashboard
        return redirect()->route('dashboard.index')->with('status', 'Job cancelled successfully.');
    }

    /**
     * Retry a failed job.
     *
     * @param  int  $id
     * @return RedirectResponse
     */
    public function retryJob($id)
    {
        $jobLog = JobLog::findOrFail($id);

        // Check if retries are allowed
        if ($jobLog->retry_count < $jobLog->max_retries) {
            $jobLog->incrementRetryCount();

            // Retry the job (adjust the job's status before retrying)
            $jobLog->update(['status' => 'retrying']);

            // Dispatch the job again after the retry delay
            $delay = $jobLog->getRetryDelayInSeconds();
            $job = (new $jobLog->job_name()) // Assuming job name is stored in the job_name field
            ->delay(now()->addSeconds($delay));

            dispatch($job);

            return redirect()->route('dashboard.index')->with('status', 'Job retrying...');
        } else {
            return redirect()->route('dashboard.index')->with('error', 'Max retries reached for this job.');
        }
    }

    /**
     * Filter jobs by status (completed, failed, running).
     *
     * @param  string  $status
     * @return RedirectResponse
     */
    public function filterByStatus($status)
    {
        return redirect()->route('dashboard.index', ['status' => $status]);
    }

    /**
     * Filter jobs by priority.
     *
     * @param  int  $priority
     * @return RedirectResponse
     */
    public function filterByPriority($priority)
    {
        return redirect()->route('dashboard.index', ['priority' => $priority]);
    }
}
