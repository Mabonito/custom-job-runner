<?php

namespace App\Services\Tests;

use Illuminate\Support\Facades\Log;
use App\Services\BackgroundJobRunner;

class BackgroundJobRunnerTest
{
    public function testRunner()
    {
         // Create an instance of BackgroundJobRunner with verbose output
        $runner = new BackgroundJobRunner(true);

        // Ensure log directories exist
        $this->ensureLogDirectories();

        // Test successful job execution
        echo "\n--- Testing Simple Method Execution ---\n";
        $result1 = $runner->run(
            TestBackgroundJob::class, 
            'simpleMethod', 
            ['test_param1', 'test_param2']
        );
        echo "Simple Method Result: " . ($result1 ? 'Success' : 'Failure') . "\n";

        // Test failing job execution
        echo "\n--- Testing Failing Method Execution ---\n";
        $result2 = $runner->run(
            TestBackgroundJob::class, 
            'failingMethod'
        );
        echo "Failing Method Result: " . ($result2 ? 'Success' : 'Failure') . "\n";

        // View logs
        echo "\n--- Log File Locations ---\n";
        echo "1. " . storage_path('logs/background_jobs.log') . "\n";
        echo "2. " . storage_path('logs/background_jobs_errors.log') . "\n";
    }

     /**
     * Ensure log directories exist
     */
    protected function ensureLogDirectories()
    {
        $logPaths = [
            storage_path('logs'),
            storage_path('logs/background_jobs.log'),
            storage_path('logs/background_jobs_errors.log')
        ];

        foreach ($logPaths as $path) {
            if (!file_exists($path)) {
                if (is_dir(dirname($path))) {
                    touch($path);
                } else {
                    mkdir(dirname($path), 0755, true);
                    touch($path);
                }
            }
        }
    }
}