<?php

namespace App\Services\Tests;

class TestBackgroundJob
{

    private static $attempts = 0;

    public function simpleMethod($param1 = null, $param2 = null)
    {
        // Simulate some work
        \Illuminate\Support\Facades\Log::info("Simple method called with params: " . json_encode(func_get_args()));
        
        return true;
    }

    public function failingMethod()
    {
        // Simulate a method that will throw an exception
        throw new \Exception("Intentional test failure in TestBackgroundJob");
    }


    public static function intermittentMethod()
    {
        self::$attempts++;

        if (self::$attempts < 3) {
            throw new \Exception("Simulated intermittent failure on attempt " . self::$attempts);
        }

        return "Success after " . self::$attempts . " attempts!";
    }
}
