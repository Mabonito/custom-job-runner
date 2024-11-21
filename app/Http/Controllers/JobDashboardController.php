<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Symfony\Component\Process\Process;

class JobDashboardController extends Controller
{
    /**
     * Show the completed jobs log.
     */
     public function index()
    {
        $jobs = File::exists(storage_path('logs/background_jobs.log'))
            ? array_reverse(file(storage_path('logs/background_jobs.log')))
            : [];

        return view('jobs.index', ['jobs' => $this->parseJobLogs($jobs)]);
    }

    /**
     * Show the error logs.
     */
    public function errors()
    {
        $errors = File::exists(storage_path('logs/background_jobs_errors.log'))
            ? array_reverse(file(storage_path('logs/background_jobs_errors.log')))
            : [];

        $parsedErrors = [];
        foreach ($errors as $error) {
            // Decode JSON string if the log entry is in JSON format
            $errorData = json_decode($error, true);
            
            // If JSON decoding failed, treat the message as a string
            if (json_last_error() !== JSON_ERROR_NONE) {
                $errorData = [
                    'job_id' => 'N/A',
                    'message' => $error,
                    'timestamp' => now(),
                    'level' => 'ERROR',
                ];
            }

            // Store parsed error data for display
            $parsedErrors[] = $errorData;
        }

        return view('jobs.errors', ['errors' => $parsedErrors]);
    }

    /**
     * Cancel a running job by PID.
     */
    public function cancel($pid)
    {
        try {
            if (stripos(PHP_OS, 'WIN') === 0) {
                // Windows command to kill process
                exec("taskkill /PID $pid /F");
            } else {
                // Unix-based command to kill process
                exec("kill -9 $pid");
            }

            return redirect()->route('jobs.index')->with('success', 'Job canceled successfully.');
        } catch (\Exception $e) {
            return redirect()->route('jobs.index')->with('error', 'Failed to cancel job: ' . $e->getMessage());
        }
    }

    /**
     * Parse job logs to extract relevant details.
     */
    private function parseJobLogs(array $logs)
    {
        return array_map(function ($log) {
            // Example log parsing (adjust according to your log format)
            preg_match('/\[(.*?)\] \[(.*?)\] (.*?): (.*)/', $log, $matches);
            return [
                'timestamp' => $matches[1] ?? '',
                'level' => $matches[2] ?? '',
                'message' => $matches[4] ?? '',
            ];
        }, $logs);
    }
}
