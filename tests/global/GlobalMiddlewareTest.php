<?php

declare(strict_types=1);

use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Panchodp\LaravelFingerprint\Fingerprint;
use Panchodp\LaravelFingerprint\Http\Middleware\FingerprintGuard;
use Panchodp\LaravelFingerprint\Tests\GlobalMiddlewareTestCase;

uses(GlobalMiddlewareTestCase::class);

$server = [
    'HTTP_USER_AGENT' => 'Mozilla/5.0',
    'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
    'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
    'REMOTE_ADDR' => '127.0.0.1',
];

it('registers middleware globally when global is enabled', function () use ($server): void {
    $request = Request::create('/', 'GET', [], [], [], $server);
    $request->setLaravelSession(app(Session::class));

    $next = fn (Request $req) => new Response('OK');

    (new FingerprintGuard(new Fingerprint($request)))->handle($request, $next);

    expect($request->session()->has('fingerprint'))->toBeTrue();
});
