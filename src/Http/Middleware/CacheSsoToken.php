<?php

namespace SengHeat\LaravelSso\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CacheSsoToken
{
    public function handle(Request $request, Closure $next, string $guard = 'api')
    {
        $token = $request->bearerToken();

        if (! $token) {
            return $next($request);
        }

        $ttl = (int) config('sso.token_cache_ttl', 300);

        if ($ttl <= 0) {
            return $next($request);
        }

        $store    = config('sso.cache_store');
        $cache    = $store ? Cache::store($store) : Cache::getFacadeRoot();
        $cacheKey = 'sso_token_' . hash('sha256', $token);

        $user = $cache->remember($cacheKey, $ttl, function () use ($token) {
            $userModel = config('sso.user_model', \App\Models\User::class);

            return $userModel::where('api_token', hash('sha256', $token))->first();
        });

        if ($user) {
            Auth::guard($guard)->setUser($user);
        }

        return $next($request);
    }
}
