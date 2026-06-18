<?php

declare(strict_types=1);

namespace Src83\LaravelApiResponse\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Src83\LaravelApiResponse\ApiResponseServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [ApiResponseServiceProvider::class];
    }
}
