---
name: laravel-fingerprint-development
description: "Use when working with panchodp/laravel-fingerprint, the session theft protection package for Laravel. Trigger whenever the query mentions the package by name, the `fingerprint` middleware, session hijacking / session theft protection, or the `LARAVEL_FINGERPRINT_*` env variables. Tasks include protecting routes with the `fingerprint` middleware, enabling global mode, configuring `include_ip` or `redirect_route`, generating a fingerprint manually, and writing Pest or PHPUnit tests that involve the middleware. Do not trigger for generic Laravel session configuration, authentication guards, CSRF, or browser/device fingerprinting libraries unrelated to this package."
license: MIT
metadata:
  author: panchodp
---

# Laravel Fingerprint Development

## When to use this skill

Use this skill when working with `panchodp/laravel-fingerprint`: adding session theft protection to routes, tuning its configuration, customizing the redirect behavior, or writing tests that involve the `fingerprint` middleware.

## How it works

1. On the first request, `FingerprintGuard` computes a SHA-256 hash from `User-Agent | Accept-Language | Accept-Encoding` (the IP is prepended when `include_ip` is enabled) and stores it in the session under the `fingerprint` key.
2. On every subsequent request the hash is recomputed and compared.
3. On mismatch, the session is invalidated and the user is redirected (302) to the named route in `redirect_route` (default `login`), or to `/` if that route does not exist.
4. When `enabled` is `false`, the middleware passes the request through untouched and stores nothing.

## Registering the middleware

The package registers the `fingerprint` alias automatically via its service provider. On authenticated routes, place it after `auth` so unauthenticated requests are redirected by the auth guard first:

```php
Route::middleware(['auth', 'fingerprint'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
});
```

Or on a single route:

```php
Route::get('/settings', SettingsController::class)
    ->middleware(['auth', 'fingerprint']);
```

To protect every `web` route without touching route files, enable global mode in `.env`:

```dotenv
LARAVEL_FINGERPRINT_GLOBAL=true
```

Global mode also covers guest routes (including `login`), which is fine: the fingerprint stored before login survives `session()->regenerate()`. Adding the `fingerprint` alias manually on top of global mode is redundant (Laravel deduplicates middleware), so pick one approach.

## Configuration

Publish the config file only when you need to change defaults beyond what env variables cover:

```shell
php artisan vendor:publish --tag=laravel-fingerprint-config
```

| Key | Env variable | Default | Guidance |
|---|---|---|---|
| `enabled` | `LARAVEL_FINGERPRINT_ENABLED` | `true` | Set to `false` to disable per environment (e.g. local). |
| `include_ip` | `LARAVEL_FINGERPRINT_INCLUDE_IP` | `false` | Keep disabled for mobile/dynamic IP users; it causes false logouts. |
| `redirect_route` | `LARAVEL_FINGERPRINT_REDIRECT_ROUTE` | `login` | Must be a named route; falls back to `/` otherwise. |
| `global` | `LARAVEL_FINGERPRINT_GLOBAL` | `false` | Pushes the middleware into the `web` group at boot time. |

## Generating a fingerprint manually

`Panchodp\LaravelFingerprint\Fingerprint` is bound in the container and depends on the current `Request`:

```php
use Panchodp\LaravelFingerprint\Fingerprint;

$hash = app(Fingerprint::class)->generateFingerPrint();
```

Use this only for logging or diagnostics. Do not store your own value in the `fingerprint` session key; the middleware owns it.

## Testing

### Unit-testing the middleware

Build a request with explicit headers and a Laravel session, then run the middleware directly:

```php
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Panchodp\LaravelFingerprint\Fingerprint;
use Panchodp\LaravelFingerprint\Http\Middleware\FingerprintGuard;

function makeRequest(array $server): Request
{
    $request = Request::create('/', 'GET', [], [], [], $server);
    $request->setLaravelSession(app(Session::class));

    return $request;
}

it('redirects when the user agent changes', function (): void {
    $server = [
        'HTTP_USER_AGENT' => 'Mozilla/5.0',
        'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
        'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
    ];
    $next = fn () => new Response('OK');

    $request = makeRequest($server);
    (new FingerprintGuard(new Fingerprint($request)))->handle($request, $next);

    $hijacked = makeRequest(['HTTP_USER_AGENT' => 'curl/7.0'] + $server);
    $hijacked->setLaravelSession($request->session());

    $response = (new FingerprintGuard(new Fingerprint($hijacked)))->handle($hijacked, $next);

    expect($response->getStatusCode())->toBe(302);
});
```

### Feature tests

Laravel's HTTP test client sends the same default `User-Agent` (`Symfony`) and `Accept-Language` on every request, so the fingerprint matches across `$this->get()` / `$this->post()` calls without extra setup. It only breaks if you change those headers between requests in the same test. To simulate a hijacked session, change the `User-Agent` explicitly:

```php
$this->actingAs($user)->get('/dashboard')->assertOk();

$this->withHeader('User-Agent', 'curl/7.0')
    ->get('/dashboard')
    ->assertRedirect(route('login'));
```

If the fingerprint is not what is under test, disable it with `config(['laravel_fingerprint.enabled' => false])`.

### Testing global mode

`global` is read in the service provider's `boot()`. Setting it with `config([...])` inside a test is too late; set it before the app boots, for example in a dedicated test case:

```php
protected function defineEnvironment($app): void
{
    $app['config']->set('laravel_fingerprint.global', true);
}
```

## Common pitfalls

- Setting `laravel_fingerprint.global` at runtime and expecting the middleware to be registered: it is only pushed to the `web` group at boot.
- Expecting a redirect loop when `redirect_route` is itself protected by `fingerprint`: there is none. Invalidating the session flushes the stored fingerprint, so the next request stores a fresh one and passes.
- Enabling `include_ip` behind a load balancer or reverse proxy without configuring trusted proxies: `$request->ip()` returns the proxy's address, not the client's, so the IP adds no protection and may vary between proxy nodes.
- Unexpected 302s in feature tests: a request in the same test changed `User-Agent`, `Accept-Language` or `Accept-Encoding`. Keep headers consistent or disable the package for that test.
