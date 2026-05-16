<?php

namespace SengHeat\LaravelSso\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CacheSsoToken
{
    public function handle(Request $request, Closure $next, int $ttl = 300)
    {
        $token = $request->bearerToken();

        if (! $token) {
            return $next($request);
        }

        $cacheKey = 'sso_token_' . hash('sha256', $token);

        Cache::remember($cacheKey, $ttl, function () use ($token) {
            $userModel = config('sso.user_model', \App\Models\User::class);

            return $userModel::where('api_token', hash('sha256', $token))->first();
        });

        return $next($request);
    }
}
