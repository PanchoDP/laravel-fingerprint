<?php

declare(strict_types=1);

use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Router;
use Panchodp\LaravelFingerprint\Fingerprint;
use Panchodp\LaravelFingerprint\Http\Middleware\FingerprintGuard;

$server = [
    'HTTP_USER_AGENT' => 'Mozilla/5.0',
    'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
    'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
    'REMOTE_ADDR' => '127.0.0.1',
];

$next = fn () => new Response('OK');

/**
 * @param  array<string, string>  $server
 */
function makeRequest(array $server): Request
{
    $request = Request::create('/', 'GET', [], [], [], $server);
    $session = app(Session::class);
    $request->setLaravelSession($session);

    return $request;
}

it('stores fingerprint in session on first request', function () use ($server, $next): void {
    $request = makeRequest($server);

    (new FingerprintGuard(new Fingerprint($request)))->handle($request, $next);

    expect($request->session()->has('fingerprint'))->toBeTrue();
});

it('allows request when fingerprint matches', function () use ($server, $next): void {
    $request = makeRequest($server);
    $guard = new FingerprintGuard(new Fingerprint($request));

    $guard->handle($request, $next);
    $response = $guard->handle($request, $next);

    expect($response->getStatusCode())->toBe(200);
});

it('redirects when fingerprint does not match', function () use ($server): void {
    config(['laravel_fingerprint.redirect_route' => 'home']);
    app(Router::class)->get('/home', fn () => 'home')->name('home');

    $next = fn () => new Response('OK');

    $request = makeRequest($server);
    (new FingerprintGuard(new Fingerprint($request)))->handle($request, $next);

    $differentRequest = Request::create('/', 'GET', [], [], [], ['HTTP_USER_AGENT' => 'curl/7.0'] + $server);
    $differentRequest->setLaravelSession($request->session());

    $response = (new FingerprintGuard(new Fingerprint($differentRequest)))->handle($differentRequest, $next);

    expect($response->getStatusCode())->toBe(302);
});

it('passes through when package is disabled', function () use ($server, $next): void {
    config(['laravel_fingerprint.enabled' => false]);

    $request = makeRequest($server);
    $response = (new FingerprintGuard(new Fingerprint($request)))->handle($request, $next);

    expect($response->getStatusCode())->toBe(200);
    expect($request->session()->has('fingerprint'))->toBeFalse();
});

it('redirects to / when redirect_route does not exist as named route', function () use ($server): void {
    config(['laravel_fingerprint.redirect_route' => 'nonexistent-route']);

    $next = fn () => new Response('OK');

    $request = makeRequest($server);
    (new FingerprintGuard(new Fingerprint($request)))->handle($request, $next);

    $differentRequest = Request::create('/', 'GET', [], [], [], ['HTTP_USER_AGENT' => 'curl/7.0'] + $server);
    $differentRequest->setLaravelSession($request->session());

    $response = (new FingerprintGuard(new Fingerprint($differentRequest)))->handle($differentRequest, $next);

    expect($response->getStatusCode())->toBe(302)
        ->and($response->headers->get('Location'))->toBe('/');
});

it('redirects when fingerprint does not match with include_ip enabled', function () use ($server): void {
    config(['laravel_fingerprint.include_ip' => true, 'laravel_fingerprint.redirect_route' => 'home']);
    app(Router::class)->get('/home', fn () => 'home')->name('home');

    $next = fn () => new Response('OK');

    $request = makeRequest($server);
    (new FingerprintGuard(new Fingerprint($request)))->handle($request, $next);

    $differentIpServer = ['REMOTE_ADDR' => '10.0.0.1'] + $server;
    $differentRequest = Request::create('/', 'GET', [], [], [], $differentIpServer);
    $differentRequest->setLaravelSession($request->session());

    $response = (new FingerprintGuard(new Fingerprint($differentRequest)))->handle($differentRequest, $next);

    expect($response->getStatusCode())->toBe(302);
});
