<?php

namespace App\Models;

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
}
