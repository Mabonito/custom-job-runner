<!DOCTYPE html>
<html>
<head>
    <title>Background Jobs</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Background Jobs</h1>
        <a href="{{ route('jobs.errors') }}" class="btn btn-danger mb-3">View Errors</a>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Job ID</th>
                    <th>Retries</th>
                    <th>Timestamp</th>
                    <th>Details</th>
                </tr>
            </thead>
           <tbody>
                @forelse ($jobs as $job)
                    @php
                        // Decode $job only once; handle both string and array cases.
                        $jobData = is_array($job) ? $job : json_decode($job, true);
                    @endphp
                    <tr>
                        <td>{{ $jobData['job_id'] ?? 'N/A' }}</td>
                        <td>{{ $jobData['retries'] ?? 0 }}</td>
                        <td>{{ $jobData['timestamp'] ?? 'N/A' }}</td>
                        <td>
                            <pre>{{ json_encode($jobData, JSON_PRETTY_PRINT) }}</pre>
                              @if (!empty($jobData['status']) && $jobData['status'] === 'running' && !empty($jobData['pid']))
                                <form method="POST" action="{{ route('jobs.cancel', $jobData['pid']) }}">
                                    @csrf
                                    <button class="btn btn-danger btn-sm">Cancel</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">No jobs found.</td>
                    </tr>
                @endforelse
            </tbody>

        </table>
    </div>
</body>
</html>
