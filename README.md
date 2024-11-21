# Custom Background Job Runner for Laravel

****

# Objective
****
The goal of this project is to design and implement a custom system to execute PHP classes as background jobs, independent of Laravel's built-in queue system. This solution demonstrates scalability, error handling, and ease of use within a Laravel application.

# Features
***
Background Job Execution: Execute PHP classes and methods in the background, separate from the main Laravel application process.
Error Handling: Catch exceptions and log errors to a dedicated log file.
Retry Mechanism: Configurable retry attempts for failed jobs.
Logging: Log job statuses, including success, failure, and progress, with timestamps.
Security: Validate and sanitize class and method names to prevent unauthorized or harmful execution.


# Installation
****

To install the custom background job runner in your Laravel application, follow the steps below.

Step 1: Clone the Repository

Clone the repository into your Laravel project:

    git clone https://github.com/Mabonito/custom-job-runner.git

Step 2: Install Dependencies

Install the necessary dependencies via Composer:

    composer install

Step 3: Configuration

Make sure to add any necessary configuration settings for your custom job runner system in the $.env file. If applicable, specify the location of the log file, retry attempts, and any other relevant configurations.
Usage
runBackgroundJob Function

The main function to run background jobs is runBackgroundJob, which is a global helper function in Laravel.
Function Signature

runBackgroundJob(string $className, string $methodName, array $params = [], int $retryAttempts = 3, int $delay = 0, int $priority = 1)

Parameters

    className: The fully qualified class name to be executed in the background.
    methodName: The method of the class to be executed.
    params: The parameters to be passed to the method.
    retryAttempts: The number of retry attempts in case the job fails (default: 3).
    delay: The number of seconds to delay the execution of the job (default: 0).
    priority: A priority value (higher value = higher priority).

Example Usage

    runBackgroundJob('App\\Services\\BackgroundJobRunner', 'processData', [$data], 3, 10, 2);

This will execute the processData method of BackgroundJobRunner class in the background, with a retry attempt of 3, a 10-second delay, and a priority of 2.

# Job Execution

Jobs are executed in the background using system commands to run them in separate processes. The job can be executed for both Windows and Unix-based systems.

    On Unix-based systems, the command runs using nohup to ensure that it continues running in the background even if the session ends.
    On Windows, the job is executed using start to open a new process.

Error Handling

    All errors encountered during job execution are logged in the background_jobs_errors.log file.
    If an exception occurs in the background process, it will be caught, and an error message will be logged.

Sample Error Log

    [2024-11-21 12:30:00] ERROR: Job failed for App\\Services\\BackgroundJobRunner::processData. Error: [Exception details]

# Retry Mechanism

Jobs that fail can be retried a configurable number of times. If a job fails, the system will attempt to run the job again, up to the specified retry limit. The retry attempts will respect the configured delay between each retry.
Job Logging

Each job's execution status, including start time, completion time, success/failure status, and any error messages, is logged into the background_jobs.log file.
Sample Log Entry

    [2024-11-21 12:30:00] INFO: Job started for App\\Services\\BackgroundJobRunner::processData.
    [2024-11-21 12:32:00] INFO: Job completed successfully for App\\Services\\BackgroundJobRunner::processData.

Security

    Only pre-approved classes and methods can be executed in the background. The system ensures that any unauthorized class or method is rejected.
    Class and method names are validated and sanitized to prevent malicious input.

Advanced Features
Web-Based Dashboard

A simple Laravel web interface is provided to manage and monitor background jobs. The dashboard includes:

    Active Jobs: View currently running
    Job Logs: View logs of completed or failed jobs.
    Retry Mechanism: View and retry failed jobs from the interface.

# Sample Configuration

    BACKGROUND_JOB_LOG=storage/logs/background_jobs.log
    ERROR_LOG=storage/logs/background_jobs_errors.log
    DEFAULT_RETRY_ATTEMPTS=3
    DEFAULT_DELAY=10
    DEFAULT_PRIORITY=1

# Testing

You can test the background job runner by executing different methods and observing the logs for success or failure. Here's an example test case:

    public function testBackgroundJobExecution()
    {
        $result = runBackgroundJob('App\\Services\\BackgroundJobRunner', 'processData', [$data]);
        $this->assertTrue($result);
    }
