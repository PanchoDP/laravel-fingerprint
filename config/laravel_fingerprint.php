<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel Fingerprint Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration options for the Laravel Fingerprint
    | package.
    |
    */

    'enabled' => env('LARAVEL_FINGERPRINT_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Include IP in Fingerprint
    |--------------------------------------------------------------------------
    |
    | When enabled, the client IP is included in the fingerprint hash.
    | Disable this for users with dynamic IPs or mobile networks.
    |
    */

    'include_ip' => env('LARAVEL_FINGERPRINT_INCLUDE_IP', false),

    /*
    |--------------------------------------------------------------------------
    | Redirect Route
    |--------------------------------------------------------------------------
    |
    | The named route to redirect to when the fingerprint does not match.
    |
    */

    'redirect_route' => env('LARAVEL_FINGERPRINT_REDIRECT_ROUTE', 'login'),

    /*
    |--------------------------------------------------------------------------
    | Apply Globally
    |--------------------------------------------------------------------------
    |
    | When enabled, the fingerprint middleware is applied to all HTTP routes
    | automatically. When disabled, apply it manually using the 'fingerprint'
    | middleware alias on the routes or groups you want to protect.
    |
    */

    'global' => env('LARAVEL_FINGERPRINT_GLOBAL', false),

];
