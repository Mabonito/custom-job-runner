<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Tests\BackgroundJobRunnerTest;

class TestBackgroundJobRunner extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-background-job-runner';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the Background Job Runner functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!class_exists(BackgroundJobRunnerTest::class)) {
            $this->error('BackgroundJobRunnerTest class not found!');
            return Command::FAILURE;
        }

        $test = new BackgroundJobRunnerTest();
        
       try {
            $this->info('Initializing test runner...');
            $test->testRunner();
            $this->info('Background Job Runner Test completed successfully.');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Test failed: ' . $e->getMessage());
            $this->error('Trace: ' . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}
