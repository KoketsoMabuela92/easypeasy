@extends('layouts.app')

@section('title', 'Job Dashboard')

@section('content')
<div class="container mt-5">
    <h2 class="text-center mb-4 text-primary">Job Dashboard</h2>

    <table id="jobsTable" class="table table-bordered table-striped table-hover">
        <thead class="bg-dark text-white">
        <tr>
            <th>Job Name</th>
            <th>Status</th>
            <th>Priority</th>
            <th>Retry Count</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        @forelse ($jobLogs as $jobLog)
        <tr>
            <td class="fw-bold">{{ $jobLog->job_name }}</td>
            <td>
                <span class="badge
                    {{ $jobLog->status === 'completed' ? 'bg-success' :
                       ($jobLog->status === 'failed' ? 'bg-danger' : 'bg-warning text-dark') }}">
                    {{ ucfirst($jobLog->status) }}
                </span>
            </td>
            <td>{{ $jobLog->priority }}</td>
            <td>{{ $jobLog->retry_count }}</td>
            <td>
                @if ($jobLog->status === 'running')
                <form action="{{ route('cancel-job', $jobLog->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm">Cancel</button>
                </form>
                @else
                <button class="btn btn-secondary btn-sm" disabled>No Actions</button>
                @endif
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="5" class="text-center text-muted">No jobs found.</td>
        </tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection

@section('styles')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endsection

@section('scripts')
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function () {
        $('#jobsTable').DataTable({
            paging: true,
            searching: true,
            ordering: true,
            language: {
                emptyTable: "No jobs available."
            }
        });
    });
</script>
@endsection
