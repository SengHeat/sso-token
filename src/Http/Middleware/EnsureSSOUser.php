<?php

namespace SengHeat\LaravelSso\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureSSOUser
{
    public function handle(Request $request, Closure $next, ?string $provider = null)
    {
        $user = Auth::user();

        if (! $user || empty($user->sso_provider)) {
            abort(403, 'This area requires SSO authentication.');
        }

        if ($provider && $user->sso_provider !== $provider) {
            abort(403, "This area requires [{$provider}] SSO authentication.");
        }

        return $next($request);
    }
}
