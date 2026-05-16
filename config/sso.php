<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SSO Providers
    |--------------------------------------------------------------------------
    | Configure each OAuth provider. Leave client_id empty to disable a provider.
    */
    'providers' => [

        'google' => [
            'client_id'     => env('GOOGLE_CLIENT_ID'),
            'client_secret' => env('GOOGLE_CLIENT_SECRET'),
            'redirect'      => env('GOOGLE_REDIRECT_URI', '/sso/google/callback'),
        ],

        'github' => [
            'client_id'     => env('GITHUB_CLIENT_ID'),
            'client_secret' => env('GITHUB_CLIENT_SECRET'),
            'redirect'      => env('GITHUB_REDIRECT_URI', '/sso/github/callback'),
        ],

        'azure' => [
            'client_id'     => env('AZURE_CLIENT_ID'),
            'client_secret' => env('AZURE_CLIENT_SECRET'),
            'redirect'      => env('AZURE_REDIRECT_URI', '/sso/azure/callback'),
            'tenant'        => env('AZURE_TENANT_ID', 'common'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Redirects
    |--------------------------------------------------------------------------
    */
    'redirect_after_login'  => env('SSO_REDIRECT_AFTER_LOGIN', '/dashboard'),
    'redirect_after_logout' => env('SSO_REDIRECT_AFTER_LOGOUT', '/login'),

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    */
    'user_model' => env('SSO_USER_MODEL', \App\Models\User::class),

    /*
    |--------------------------------------------------------------------------
    | Form Auth (Issuer Mode)
    |--------------------------------------------------------------------------
    | Enable built-in login/register forms and API endpoints.
    | Set SSO_FORM_AUTH=true on the service that issues tokens (auth-service).
    | Other services (order-service, etc.) leave this false — they only verify.
    |
    | SSO_FORM_AUTH          — enable form login/register routes + views
    | SSO_FORM_AUTH_REGISTER — allow new user registration (default: true)
    */
    'form_auth' => [
        'enabled'        => env('SSO_FORM_AUTH', false),
        'allow_register' => env('SSO_FORM_AUTH_REGISTER', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    | Set false to register your own SSO routes manually.
    */
    'register_routes' => true,

    /*
    |--------------------------------------------------------------------------
    | Migrations
    |--------------------------------------------------------------------------
    | Set false to manage migrations yourself.
    */
    'run_migrations' => true,

    /*
    |--------------------------------------------------------------------------
    | Token Cache
    |--------------------------------------------------------------------------
    | SSO_CACHE_STORE  — which cache store to use (redis, memcached, file, etc.)
    |                    null = use the app's default cache store.
    | SSO_TOKEN_CACHE_TTL — seconds to cache verified api_token lookups.
    |                       Set to 0 to disable. Recommended: 300 (5 min).
    */
    'cache_store'     => env('SSO_CACHE_STORE', null),
    'token_cache_ttl' => (int) env('SSO_TOKEN_CACHE_TTL', 300),

    /*
    |--------------------------------------------------------------------------
    | Event Listeners
    |--------------------------------------------------------------------------
    | Map SSO events to your own listener classes.
    */
    'listeners' => [
        \SengHeat\LaravelSso\Events\SSOLoginSucceeded::class => [],
        \SengHeat\LaravelSso\Events\SSOLoginFailed::class    => [],
        \SengHeat\LaravelSso\Events\SSOUserCreated::class    => [],
    ],

];
