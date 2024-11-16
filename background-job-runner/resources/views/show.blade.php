@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Job Log Details: {{ $job->job_name }} - {{ $job->method_name }}</h1>

    <div>
        <h3>Status: {{ $job->status }}</h3>
        <p><strong>Error Message:</strong> {{ $job->error_message ?? 'N/A' }}</p>
        <p><strong>Retry Count:</strong> {{ $job->retry_count }} / {{ $job->max_retries }}</p>
        <p><strong>Priority:</strong> {{ $job->priority }}</p>
        <p><strong>Delay (seconds):</strong> {{ $job->retry_delay }}</p>
        <p><strong>Timeout (seconds):</strong> {{ $job->timeout }}</p>
    </div>

    <a href="{{ route('dashboard.index') }}" class="btn btn-primary">Back to Dashboard</a>
</div>
@endsection
