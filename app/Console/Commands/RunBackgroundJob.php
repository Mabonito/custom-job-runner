<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BackgroundJobRunner;

class RunBackgroundJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'background:run {className} {method} {params?} {priority=0} {delay=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run a background job';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
         $className = $this->argument('className');
        $method = $this->argument('method');
        $params = json_decode($this->argument('params'), true) ?: [];
        $priority = (int) $this->argument('priority');
        $delay = (int) $this->argument('delay');

        $runner = new BackgroundJobRunner();
        $result = $runner->run($className, $method, $params, $priority, $delay);

        $this->info($result ? "Job executed successfully" : "Job execution failed");
    }
}
