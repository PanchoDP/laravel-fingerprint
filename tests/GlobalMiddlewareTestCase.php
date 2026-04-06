<?php

declare(strict_types=1);

namespace Panchodp\LaravelFingerprint\Tests;

use Illuminate\Config\Repository;

class GlobalMiddlewareTestCase extends TestCase
{
    protected function defineEnvironment($app): void
    {
        $app->make(Repository::class)->set('laravel_fingerprint.global', true);
    }
}
