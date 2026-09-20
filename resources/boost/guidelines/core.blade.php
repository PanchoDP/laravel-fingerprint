@php
/** @var \Laravel\Boost\Install\GuidelineAssist $assist */
@endphp
# Laravel Fingerprint

This package protects against session hijacking. It builds a SHA-256 fingerprint from the client's request headers (`User-Agent | Accept-Language | Accept-Encoding`, optionally prefixed with the IP) and stores it in the session. If the fingerprint changes mid-session, the session is invalidated and the user is redirected.

## Features

- Middleware alias `fingerprint` (`Panchodp\LaravelFingerprint\Http\Middleware\FingerprintGuard`): protect routes or groups explicitly. Typically placed after `auth` on authenticated routes.

@verbatim
<code-snippet name="Protect a route group with the fingerprint middleware" lang="php">
Route::middleware(['auth', 'fingerprint'])->group(function () {
    // protected routes
});
</code-snippet>
@endverbatim

- Global mode: set `LARAVEL_FINGERPRINT_GLOBAL=true` to push the middleware into the `web` group automatically. Adding the `fingerprint` alias manually on top of global mode is redundant (Laravel deduplicates middleware), so pick one approach.
- Configuration lives in `config/laravel_fingerprint.php` (publish with `{{ $assist->artisanCommand('vendor:publish --tag=laravel-fingerprint-config') }}`). Keys: `enabled`, `include_ip`, `redirect_route`, `global`. Prefer the matching `LARAVEL_FINGERPRINT_*` env variables over editing the file.
- `Panchodp\LaravelFingerprint\Fingerprint` is bound in the container; resolve it and call `generateFingerPrint()` to get the current request's hash.

## Conventions

- Keep `include_ip` disabled unless the app serves users with stable IPs; mobile and dynamic IPs cause false invalidations.
- `redirect_route` must be a named route. If it does not exist, the package falls back to `/`.
- The session key used is `fingerprint`. Do not write to it from application code.
- The middleware is a no-op when `laravel_fingerprint.enabled` is `false`; use that to disable it in specific environments instead of removing the middleware.
- `global` is read once when the service provider boots. Changing it at runtime (e.g. `config([...])` inside a test) has no effect; set it through the environment or the test case's `defineEnvironment()`.
- Read the `laravel-fingerprint-development` skill for testing patterns and common pitfalls.
