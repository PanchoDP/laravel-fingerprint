<?php

declare(strict_types=1);

namespace Panchodp\LaravelFingerprint\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Panchodp\LaravelFingerprint\LaravelFingerprintServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            LaravelFingerprintServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        // Define environment configuration
    }
}
