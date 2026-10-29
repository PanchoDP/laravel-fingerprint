<?php

declare(strict_types=1);

namespace Panchodp\LaravelFingerprint\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Panchodp\LaravelFingerprint\Fingerprint;
use Symfony\Component\HttpFoundation\Response;

final class FingerprintGuard
{
    public function __construct(
        private readonly Fingerprint $fingerprint,
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('laravel_fingerprint.enabled', true)) {
            return $next($request);
        }

        $fingerprint = $this->fingerprint->generateFingerPrint();

        if (! $request->session()->has('fingerprint')) {
            $request->session()->put('fingerprint', $fingerprint);

            return $next($request);
        }

        if ($request->session()->get('fingerprint') !== $fingerprint) {
            $this->logoutSessionGuards();
            $request->session()->invalidate();

            if ($request->expectsJson()) {
                return new JsonResponse(['message' => 'Unauthenticated.'], 401);
            }

            $route = config('laravel_fingerprint.redirect_route', 'login');
            $route = is_string($route) ? $route : 'login';

            $url = Route::has($route) ? route($route) : '/';

            return new RedirectResponse($url);
        }

        return $next($request);
    }

    /**
     * Log out every session-based guard so "remember me" tokens are cycled
     * and their cookies cleared, preventing automatic re-authentication.
     */
    private function logoutSessionGuards(): void
    {
        $guards = config('auth.guards', []);

        if (! is_array($guards)) {
            return;
        }

        foreach ($guards as $name => $guard) {
            if (! is_array($guard) || ($guard['driver'] ?? null) !== 'session') {
                continue;
            }

            $instance = Auth::guard((string) $name);

            if ($instance instanceof StatefulGuard) {
                $instance->logout();
            }
        }
    }
}
