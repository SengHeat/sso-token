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

        $userModel = config('sso.user_model', \App\Models\User::class);

        // Cache only raw attributes (plain array) — never the Eloquent object.
        // Serialising a model class causes __PHP_Incomplete_Class on retrieval
        // when the consumer service has a different autoload context.
        $attributes = $cache->remember($cacheKey, $ttl, function () use ($token, $userModel) {
            $user = $userModel::where('api_token', hash('sha256', $token))->first();

            return $user ? $user->getAttributes() : null;
        });

        if ($attributes) {
            /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
            $user = (new $userModel)->forceFill($attributes);
            $user->exists = true;
            Auth::guard($guard)->setUser($user);
        }

        return $next($request);
    }
}
