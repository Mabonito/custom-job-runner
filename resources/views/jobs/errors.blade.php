<!DOCTYPE html>
<html>
    <head>
        <title>Job Errors</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    </head>
    <body>
        <div class="container mt-5">
            <h1 class="mb-4">Job Errors</h1>
            <a href="{{ route('jobs.index') }}" class="btn btn-primary mb-3">View Jobs</a>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Job ID</th>
                        <th>Error Details</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($errors as $error)
                        <tr>
                            <td>{{ $error['job_id'] ?? 'N/A' }}</td>
                            <td><pre>{{ $error['message'] ?? 'No Message Available' }}</pre></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2">No errors found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </body>
</html>
