<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobLog extends Model
{
    use HasFactory;

    // Define the table associated with the model
    protected $table = 'job_logs';

    // Define the fillable attributes for mass assignment
    protected $fillable = [
        'job_name',
        'method_name',
        'status',
        'error_message',
        'retry_count',
        'priority',
        'max_retries',
        'retry_delay',
        'timeout',
    ];

    // Define the default values for the attributes
    protected $attributes = [
        'status' => 'pending', // Default status when job is first created
        'retry_count' => 0,    // Default retry count
    ];

    /**
     * Scope a query to only include completed jobs.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include failed jobs.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope a query to only include running jobs.
     */
    public function scopeRunning($query)
    {
        return $query->where('status', 'running');
    }

    /**
     * Scope a query to only include cancelled jobs.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Increment the retry count for the job.
     */
    public function incrementRetryCount()
    {
        $this->increment('retry_count');
        $this->save();
    }

    /**
     * Mark the job as completed.
     */
    public function markAsCompleted()
    {
        $this->update(['status' => 'completed']);
    }

    /**
     * Mark the job as failed and log the error message.
     *
     * @param  string  $errorMessage
     */
    public function markAsFailed($errorMessage)
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Mark the job as cancelled.
     */
    public function markAsCancelled()
    {
        $this->update(['status' => 'cancelled']);
    }

    /**
     * Scope a query to only include jobs with a specific priority.
     *
     * @param Builder $query
     * @param int $priority
     * @return Builder
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Return the delay in seconds before the job can be retried.
     *
     * @return int
     */
    public function getRetryDelayInSeconds()
    {
        return $this->retry_delay;
    }

    /**
     * Queue the job and ensure it's processed.
     *
     * @param  callable  $job
     * @param  int  $priority
     * @return void
     */
    public function queueJobWithPriority(callable $job, int $priority = 0)
    {
        $this->update(['priority' => $priority]);

        // Add the job to the queue, respecting priority
        \Illuminate\Support\Facades\Queue::pushOn('default', function () use ($job) {
            $job();
        });
    }
}

