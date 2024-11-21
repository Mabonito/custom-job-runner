<?php

use App\Services\BackgroundJobRunner;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

if (!function_exists('runBackgroundJob')) {
    /**
     * Run a job in the background across different platforms
     * 
     * @param string $className
     * @param string $method
     * @param array $params
     * @param int $priority
     * @param int $delay
     * @return bool
     */
    function runBackgroundJob(string $className, string $method, array $params = [], int $priority = 0, int $delay = 0)
    {
        // Determine platform and execute accordingly
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return runBackgroundJobWindows($className, $method, $params, $priority, $delay);
        } else {
            return runBackgroundJobUnix($className, $method, $params, $priority, $delay);
        }
    }

    /**
     * Run background job on Windows
     */
    function runBackgroundJobWindows(string $className, string $method, array $params = [], int $priority = 0, int $delay = 0)
    {
        $phpPath = PHP_BINARY;
        $artisanPath = base_path('artisan');
        
        $command = sprintf(
            'start /B %s %s background:run "%s" "%s" "%s" %d %d',
            $phpPath,
            $artisanPath,
            $className,
            $method,
            json_encode($params),
            $priority,
            $delay
        );

        pclose(popen($command, 'r'));
        return true;
    }

    /**
     * Run background job on Unix-like systems
     */
    function runBackgroundJobUnix(string $className, string $method, array $params = [], int $priority = 0, int $delay = 0)
    {
        $phpPath = PHP_BINARY;
        $artisanPath = base_path('artisan');
        
        $command = sprintf(
            '%s %s background:run "%s" "%s" "%s" %d %d > /dev/null 2>&1 &',
            $phpPath,
            $artisanPath,
            $className,
            $method,
            json_encode($params),
            $priority,
            $delay
        );

        exec($command);
        return true;
    }
}