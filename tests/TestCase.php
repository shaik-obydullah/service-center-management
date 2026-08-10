<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Create the application and resolve its console kernel.
     *
     * When tests run inside Docker, docker-compose injects the project's .env
     * into $_SERVER, which takes precedence over the phpunit.xml <env> values
     * in Laravel's environment repository. Reconcile $_SERVER from the values
     * PHPUnit already applied via $_ENV so the app boots in the testing env.
     */
    public function createApplication(): \Illuminate\Foundation\Application
    {
        foreach ($_ENV as $key => $value) {
            if (isset($_SERVER[$key]) && $_SERVER[$key] !== $value) {
                $_SERVER[$key] = $value;
            }
        }

        return parent::createApplication();
    }
}
