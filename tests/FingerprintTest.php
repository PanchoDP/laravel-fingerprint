<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Panchodp\LaravelFingerprint\Fingerprint;

$server = [
    'HTTP_USER_AGENT' => 'Mozilla/5.0',
    'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
    'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
    'REMOTE_ADDR' => '127.0.0.1',
];

it('generates a sha256 fingerprint', function () use ($server): void {
    $fingerprint = (new Fingerprint(Request::create('/', 'GET', [], [], [], $server)))->generateFingerPrint();

    expect($fingerprint)->toBeString()->toHaveLength(64);
});

it('generates the same fingerprint for identical requests', function () use ($server): void {
    $fp1 = (new Fingerprint(Request::create('/', 'GET', [], [], [], $server)))->generateFingerPrint();
    $fp2 = (new Fingerprint(Request::create('/', 'GET', [], [], [], $server)))->generateFingerPrint();

    expect($fp1)->toBe($fp2);
});

it('generates different fingerprints for different user agents', function () use ($server): void {
    $fp1 = (new Fingerprint(Request::create('/', 'GET', [], [], [], $server)))->generateFingerPrint();
    $fp2 = (new Fingerprint(Request::create('/', 'GET', [], [], [], ['HTTP_USER_AGENT' => 'curl/7.0'] + $server)))->generateFingerPrint();

    expect($fp1)->not->toBe($fp2);
});

it('generates different fingerprints for different accept-language', function () use ($server): void {
    $fp1 = (new Fingerprint(Request::create('/', 'GET', [], [], [], $server)))->generateFingerPrint();
    $fp2 = (new Fingerprint(Request::create('/', 'GET', [], [], [], ['HTTP_ACCEPT_LANGUAGE' => 'es-MX,es;q=0.9'] + $server)))->generateFingerPrint();

    expect($fp1)->not->toBe($fp2);
});

it('does not include ip in fingerprint by default', function () use ($server): void {
    $fp1 = (new Fingerprint(Request::create('/', 'GET', [], [], [], $server)))->generateFingerPrint();
    $fp2 = (new Fingerprint(Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '192.168.1.1'] + $server)))->generateFingerPrint();

    expect($fp1)->toBe($fp2);
});

it('includes ip in fingerprint when include_ip is enabled', function () use ($server): void {
    config(['laravel_fingerprint.include_ip' => true]);

    $fp1 = (new Fingerprint(Request::create('/', 'GET', [], [], [], $server)))->generateFingerPrint();
    $fp2 = (new Fingerprint(Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '192.168.1.1'] + $server)))->generateFingerPrint();

    expect($fp1)->not->toBe($fp2);
});
