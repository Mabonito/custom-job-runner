<?php


namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use ReflectionException;

class BackgroundJobRunner
{
    protected $logFile;
    protected $errorLogFile;
    protected $maxRetries;
    protected $verbose;

    public function __construct(bool $verbose = true)
    {
        $this->logFile = storage_path('logs/background_jobs.log');
        $this->errorLogFile = storage_path('logs/background_jobs_errors.log');
        $this->maxRetries = config('background_jobs.max_retries', 3);
        $this->verbose = $verbose;
    }

    /**
     * Execute a background job
     * 
     * @param string $className
     * @param string $method
     * @param array $params
     * @param int $priority
     * @param int $delay
     * @param int $currentRetry
     * @return bool
     */
    public function run(string $className, string $method, array $params = [], int $priority = 0, int $delay = 0, int $currentRetry = 0)
    {
        // Verbose logging
        $this->log("Attempting to run job: $className::$method (Retry: $currentRetry)");

        // Validate class and method
        if (!$this->validateJob($className, $method)) {
            $errorMessage = "Invalid job: $className::$method";
            $this->log($errorMessage, 'error');
            return false;
        }

        // Delay execution if specified
        if ($delay > 0) {
            $this->log("Delaying job execution by $delay seconds");
            sleep($delay);
        }

        $jobId = uniqid();
        $startTime = microtime(true);

        try {
            $this->logJobStart($jobId, $className, $method, $params, $priority);

            $reflectionClass = new ReflectionClass($className);
            $instance = $reflectionClass->newInstance();
            $reflectionMethod = $reflectionClass->getMethod($method);

            // Execute method with parameters
            $result = $reflectionMethod->invokeArgs($instance, $params);

            $this->logJobCompletion($jobId, $startTime, true);
            $this->log("Job completed successfully: $className::$method");
            return true;
        } catch (Exception $e) {
            // Detailed error logging
            $errorDetails = [
                'job_id' => $jobId,
                'class' => $className,
                'method' => $method,
                'params' => $params,
                'retry_count' => $currentRetry,
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]
            ];

            // Log comprehensive error information
            $this->log("Job Execution Failed: " . json_encode($errorDetails), 'error');

            // Handle job failure with retry mechanism
            return $this->handleJobFailure(
                $jobId, 
                $className, 
                $method, 
                $e, 
                $startTime, 
                $currentRetry
            );
        }
    }

     /**
     * Log job start details
     */
    protected function logJobStart(string $jobId, string $className, string $method, array $params, int $priority)
    {
        $logEntry = json_encode([
            'job_id' => $jobId,
            'class' => $className,
            'method' => $method,
            'params' => $params,
            'priority' => $priority,
            'start_time' => date('Y-m-d H:i:s')
        ]);

        File::append($this->logFile, $logEntry . PHP_EOL);
    }

    /**
     * Log job completion
     */
    protected function logJobCompletion(string $jobId, float $startTime, bool $success)
    {
        $duration = microtime(true) - $startTime;
        $logEntry = json_encode([
            'job_id' => $jobId,
            'status' => $success ? 'completed' : 'failed',
            'duration' => round($duration, 4),
            'end_time' => date('Y-m-d H:i:s')
        ]);

        File::append($this->logFile, $logEntry . PHP_EOL);
    }

    /**
     * Validate job class and method
     */
    protected function validateJob(string $className, string $method): bool
    {
        try {
            // More comprehensive validation
            $reflectionClass = new ReflectionClass($className);
            
            // Check if class exists and is instantiable
            if (!$reflectionClass->isInstantiable()) {
                $this->log("Class is not instantiable: $className", 'error');
                return false;
            }

            // Check if method exists
            if (!$reflectionClass->hasMethod($method)) {
                $this->log("Method does not exist: $method", 'error');
                return false;
            }

            // Check method accessibility
            $reflectionMethod = $reflectionClass->getMethod($method);
            if (!$reflectionMethod->isPublic()) {
                $this->log("Method is not public: $method", 'error');
                return false;
            }

            return true;
        } catch (ReflectionException $e) {
            $this->log("Reflection error: " . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Log generic errors
     */
    protected function logError(string $message)
    {
        File::append($this->errorLogFile, json_encode([
            'error' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ]) . PHP_EOL);
    }

    /**
     * Handle job failure with retry mechanism
     */
    protected function handleJobFailure(
        string $jobId, 
        string $className, 
        string $method, 
        Exception $exception, 
        float $startTime, 
        int $currentRetry = 0
    ): bool {
        // Log failure details
        $this->log("Job Failure - ID: $jobId, Class: $className, Method: $method", 'error');
        $this->log("Error: " . $exception->getMessage(), 'error');

        // Completion logging
        $this->logJobCompletion($jobId, $startTime, false);

        // Retry mechanism
        if ($currentRetry < $this->maxRetries) {
            $nextRetry = $currentRetry + 1;
            $delay = pow(2, $currentRetry); // Exponential backoff

            $this->log("Retry attempt $nextRetry for job. Delay: $delay seconds", 'warning');

            // Recursive retry with increased retry count
            return $this->run(
                $className, 
                $method, 
                [], 
                0, 
                $delay, 
                $nextRetry
            );
        }

        $this->log("Max retries reached for job. Giving up.", 'error');
        return false;
    }

     /**
     * Verbose logging method
     */
    protected function log(string $message, string $type = 'info')
    {
        // Console output
        $timestamp = date('Y-m-d H:i:s');
        $formattedMessage = "[{$timestamp}] [" . strtoupper($type) . "] " . $message;
        
        if ($this->verbose) {
            echo $formattedMessage . PHP_EOL;
        }

        // Log to Laravel log file
        match($type) {
            'error' => Log::error($message),
            'warning' => Log::warning($message),
            default => Log::info($message)
        };

        // Append to log file
        $logPath = $type === 'error' ? $this->errorLogFile : $this->logFile;
        File::append($logPath, $formattedMessage . PHP_EOL);
    }

}