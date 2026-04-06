<?php

declare(strict_types=1);

namespace Panchodp\LaravelFingerprint\Tests\Support;

use Illuminate\Config\Repository;
use Panchodp\LaravelFingerprint\Tests\TestCase;

class GlobalMiddlewareTestCase extends TestCase
{
    protected function defineEnvironment($app): void
    {
        $app->make(Repository::class)->set('laravel_fingerprint.global', true);
    }
}