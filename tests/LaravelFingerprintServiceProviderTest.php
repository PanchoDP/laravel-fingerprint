<?php

declare(strict_types=1);

use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

$server = [
    'HTTP_USER_AGENT' => 'Mozilla/5.0',
    'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
    'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
    'REMOTE_ADDR' => '127.0.0.1',
];

it('does not register middleware globally when global is disabled', function () use ($server): void {
    config(['laravel_fingerprint.global' => false]);

    $request = Request::create('/', 'GET', [], [], [], $server);
    $request->setLaravelSession(app(Session::class));

    $next = fn (Request $req) => new Response('OK');

    $next($request);

    expect($request->session()->has('fingerprint'))->toBeFalse();
});
