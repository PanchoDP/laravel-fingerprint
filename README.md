<p align="center"><a target="_blank"> <img alt="Logo for Laravel Fingerprint Package" src="art/Laravel-Fingerprint.webp"></a></p>

# Laravel Fingerprint

<p align="center">
<img src="https://img.shields.io/badge/PHP-8.4%2B-blue" alt="PHP">
<a href="https://packagist.org/packages/panchodp/laravel-fingerprint"><img src="https://img.shields.io/packagist/dt/panchodp/laravel-fingerprint" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/panchodp/laravel-fingerprint"><img src="https://img.shields.io/packagist/v/panchodp/laravel-fingerprint.svg" alt="Latest Stable Version"></a>
<img src="https://img.shields.io/badge/License-MIT-green" alt="License">
<a href="https://github.com/PanchoDP/laravel-fingerprint/actions/workflows/tests.yml"><img src="https://github.com/PanchoDP/laravel-fingerprint/actions/workflows/tests.yml/badge.svg" alt="Tests"></a>
</p>

Protects against session hijacking by generating a fingerprint from the client's request headers. If the fingerprint changes mid-session, the session is invalidated and the user is redirected.

## Requirements

- PHP ^8.4
- Laravel 12 or 13

## Installation

```bash
composer require panchodp/laravel-fingerprint
```

## Usage

Add the `fingerprint` middleware to the routes you want to protect:

```php
Route::middleware(['auth', 'fingerprint'])->group(function () {
    // protected routes
});
```

Or enable it globally for all `web` routes via the `LARAVEL_FINGERPRINT_GLOBAL=true` environment variable (see [Configuration](#configuration)).

On the first request, the fingerprint is stored in the session. On subsequent requests, it is compared — if it doesn't match, the session is invalidated and the user is redirected.

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=laravel-fingerprint-config
```

Available options in `config/laravel_fingerprint.php`:

| Key | Env variable | Default | Description |
|---|---|---|---|
| `enabled` | `LARAVEL_FINGERPRINT_ENABLED` | `true` | Enable or disable the package |
| `include_ip` | `LARAVEL_FINGERPRINT_INCLUDE_IP` | `false` | Include the client IP in the fingerprint (not recommended for mobile/dynamic IPs) |
| `redirect_route` | `LARAVEL_FINGERPRINT_REDIRECT_ROUTE` | `login` | Named route to redirect to when the fingerprint doesn't match |
| `global` | `LARAVEL_FINGERPRINT_GLOBAL` | `false` | Apply the middleware automatically to all routes in the `web` middleware group |

## How it works

The fingerprint is a SHA-256 hash of:

```
UserAgent | Accept-Language | Accept-Encoding [ | IP ]
```

If a session cookie is stolen and used from a different device or browser, the fingerprint won't match and the session will be invalidated.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
