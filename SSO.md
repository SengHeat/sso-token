# Laravel SSO Package — Complete Guide

A simple, flexible **Single Sign-On (SSO)** package for Laravel supporting **Google**, **GitHub**, **Azure AD**, and any OAuth2/OpenID Connect provider.

---

## Table of Contents

1. [What is SSO?](#what-is-sso)
2. [Package Structure](#package-structure)
3. [Requirements](#requirements)
4. [Installation (For Users)](#installation-for-users)
5. [Configuration](#configuration)
6. [Usage in Blade](#usage-in-blade)
7. [Routes](#routes)
8. [Events](#events)
9. [Middleware](#middleware)
10. [Facade](#facade)
11. [Trait Helpers](#trait-helpers)
12. [Publishing Assets](#publishing-assets)
13. [Testing](#testing)
14. [Build the Package from Scratch](#build-the-package-from-scratch)
15. [Publish to Packagist](#publish-to-packagist)

---

## What is SSO?

**Single Sign-On (SSO)** lets a user authenticate once with a central **Identity Provider (IdP)** — like Google or Azure AD — and gain access to your Laravel app (the **Service Provider**) without a separate username/password.

```
User → Your Laravel App → Google/Azure/GitHub (IdP) → Authenticated ✓
```

---

## Package Structure

```
yourvendor/laravel-sso/
├── composer.json
├── phpunit.xml
├── README.md
│
├── config/
│   └── sso.php
│
├── routes/
│   └── sso.php
│
├── database/
│   └── migrations/
│       └── 2024_01_01_000000_add_sso_columns_to_users_table.php
│
├── resources/
│   └── views/
│       └── auth/
│           └── login.blade.php
│
├── src/
│   ├── SSOServiceProvider.php
│   ├── SSOManager.php
│   ├── Contracts/
│   │   └── SSOManagerContract.php
│   ├── Facades/
│   │   └── SSO.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── SSOController.php
│   │   └── Middleware/
│   │       └── EnsureSSOUser.php
│   ├── Traits/
│   │   └── HasSSOProfile.php
│   ├── Events/
│   │   ├── SSOLoginSucceeded.php
│   │   ├── SSOLoginFailed.php
│   │   └── SSOUserCreated.php
│   ├── Exceptions/
│   │   └── ProviderNotConfiguredException.php
│   └── Commands/
│       ├── InstallSSOCommand.php
│       └── PublishSSOCommand.php
│
└── tests/
    ├── Unit/
    │   └── SSOManagerTest.php
    └── Feature/
        └── SSOControllerTest.php
```

---

## Requirements

| Requirement | Version |
|---|---|
| PHP | ^8.1 |
| Laravel | ^10.0 or ^11.0 |
| laravel/socialite | ^5.0 |

---

## Installation (For Users)

### Step 1 — Install via Composer

```bash
composer require yourvendor/laravel-sso
```

> Laravel's **auto-discovery** registers the service provider automatically. No manual changes to `config/app.php` needed.

### Step 2 — Run the install wizard

```bash
php artisan sso:install
```

This command will:
- Publish `config/sso.php`
- Publish migrations
- Optionally publish views
- Optionally run `php artisan migrate`

### Step 3 — Add the trait to your User model

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use YourVendor\LaravelSSO\Traits\HasSSOProfile;

class User extends Authenticatable
{
    use HasSSOProfile;

    protected $fillable = [
        'name',
        'email',
        'password',
        'sso_provider',
        'sso_provider_id',
        'sso_token',
        'sso_avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'sso_token',
    ];
}
```

---

## Configuration

### `.env` — Add your OAuth credentials

```env
# ─── Google ───────────────────────────────────────────────
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI=https://yourapp.com/sso/google/callback

# ─── GitHub ───────────────────────────────────────────────
GITHUB_CLIENT_ID=your-github-client-id
GITHUB_CLIENT_SECRET=your-github-client-secret
GITHUB_REDIRECT_URI=https://yourapp.com/sso/github/callback

# ─── Azure AD ─────────────────────────────────────────────
AZURE_CLIENT_ID=your-azure-client-id
AZURE_CLIENT_SECRET=your-azure-client-secret
AZURE_REDIRECT_URI=https://yourapp.com/sso/azure/callback
AZURE_TENANT_ID=your-tenant-id

# ─── SSO Behaviour ────────────────────────────────────────
SSO_REDIRECT_AFTER_LOGIN=/dashboard
SSO_REDIRECT_AFTER_LOGOUT=/login
```

### `config/sso.php` — Full config reference

```php
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
    | Event Listeners
    |--------------------------------------------------------------------------
    | Map SSO events to your own listener classes.
    */
    'listeners' => [
        \YourVendor\LaravelSSO\Events\SSOLoginSucceeded::class => [],
        \YourVendor\LaravelSSO\Events\SSOLoginFailed::class    => [],
        \YourVendor\LaravelSSO\Events\SSOUserCreated::class    => [],
    ],

];
```

---

## Usage in Blade

### Option A — Pre-built view (renders all enabled providers automatically)

```blade
@include('sso::auth.login')
```

### Option B — Manual links

```blade
<a href="{{ route('sso.redirect', 'google') }}">Sign in with Google</a>
<a href="{{ route('sso.redirect', 'github') }}">Sign in with GitHub</a>
<a href="{{ route('sso.redirect', 'azure') }}">Sign in with Microsoft</a>

{{-- Logout --}}
<form method="POST" action="{{ route('sso.logout') }}">
    @csrf
    <button type="submit">Logout</button>
</form>
```

### Option C — Dynamic (loop over enabled providers)

```blade
@foreach (SSO::enabledProviders() as $provider)
    <a href="{{ route('sso.redirect', $provider) }}">
        Sign in with {{ ucfirst($provider) }}
    </a>
@endforeach
```

---

## Routes

The package auto-registers these routes under the `web` middleware group:

| Method | URI | Route Name | Description |
|---|---|---|---|
| GET | `/sso/{provider}/redirect` | `sso.redirect` | Redirects user to the IdP |
| GET | `/sso/{provider}/callback` | `sso.callback` | Handles IdP callback + login |
| POST | `/sso/logout` | `sso.logout` | Clears the local session |

To disable auto-registration and define your own routes:

```php
// config/sso.php
'register_routes' => false,
```

Then in `routes/web.php`:

```php
use YourVendor\LaravelSSO\Http\Controllers\SSOController;

Route::middleware(['web'])->group(function () {
    Route::get('/auth/{provider}/redirect', [SSOController::class, 'redirect'])->name('sso.redirect');
    Route::get('/auth/{provider}/callback', [SSOController::class, 'callback'])->name('sso.callback');
    Route::post('/auth/logout', [SSOController::class, 'logout'])->name('sso.logout');
});
```

---

## Events

The package fires three events you can listen to for custom business logic.

### Register listeners in `EventServiceProvider`

```php
<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use YourVendor\LaravelSSO\Events\SSOLoginFailed;
use YourVendor\LaravelSSO\Events\SSOLoginSucceeded;
use YourVendor\LaravelSSO\Events\SSOUserCreated;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [

        // Fires on every successful SSO login
        SSOLoginSucceeded::class => [
            \App\Listeners\LogSuccessfulSSOLogin::class,
        ],

        // Fires when SSO fails (bad token, cancelled, etc.)
        SSOLoginFailed::class => [
            \App\Listeners\AlertAdminOnSSOFailure::class,
        ],

        // Fires only when a brand-new user account is created via SSO
        SSOUserCreated::class => [
            \App\Listeners\SendWelcomeEmail::class,
            \App\Listeners\AssignDefaultRole::class,
        ],

    ];
}
```

### Event payloads

**`SSOLoginSucceeded`**
```php
public readonly User   $user;      // The authenticated user
public readonly string $provider;  // 'google' | 'github' | 'azure'
public readonly bool   $isNewUser; // true if account was just created
```

**`SSOLoginFailed`**
```php
public readonly string $provider; // Which provider failed
public readonly string $reason;   // Exception message
```

**`SSOUserCreated`**
```php
public readonly User   $user;     // The newly created user
public readonly string $provider; // Which provider was used
```

### Example listeners

**Send a welcome email on first SSO login:**

```php
<?php

namespace App\Listeners;

use App\Mail\WelcomeMail;
use Illuminate\Support\Facades\Mail;
use YourVendor\LaravelSSO\Events\SSOUserCreated;

class SendWelcomeEmail
{
    public function handle(SSOUserCreated $event): void
    {
        Mail::to($event->user->email)
            ->queue(new WelcomeMail($event->user));
    }
}
```

**Assign a default role (using spatie/laravel-permission):**

```php
<?php

namespace App\Listeners;

use YourVendor\LaravelSSO\Events\SSOUserCreated;

class AssignDefaultRole
{
    public function handle(SSOUserCreated $event): void
    {
        $event->user->assignRole('member');
    }
}
```

**Log every SSO login:**

```php
<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use YourVendor\LaravelSSO\Events\SSOLoginSucceeded;

class LogSuccessfulSSOLogin
{
    public function handle(SSOLoginSucceeded $event): void
    {
        Log::info('SSO login', [
            'user_id'  => $event->user->id,
            'provider' => $event->provider,
            'new_user' => $event->isNewUser,
        ]);
    }
}
```

---

## Middleware

The package provides an `EnsureSSOUser` middleware to restrict routes to SSO-authenticated users only.

### Step 1 — Register the middleware

**Laravel 10 — `app/Http/Kernel.php`:**

```php
protected $middlewareAliases = [
    // ...
    'sso.only' => \YourVendor\LaravelSSO\Http\Middleware\EnsureSSOUser::class,
];
```

**Laravel 11 — `bootstrap/app.php`:**

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'sso.only' => \YourVendor\LaravelSSO\Http\Middleware\EnsureSSOUser::class,
    ]);
})
```

### Step 2 — Use in routes

```php
// Any SSO user
Route::middleware(['auth', 'sso.only'])->group(function () {
    Route::get('/dashboard', DashboardController::class);
});

// Only Google SSO users
Route::middleware(['auth', 'sso.only:google'])->get('/google-only', fn () => 'Hello Google!');

// Only Azure AD users (corporate area)
Route::middleware(['auth', 'sso.only:azure'])->prefix('admin')->group(function () {
    Route::get('/', AdminController::class);
});
```

---

## Facade

```php
use YourVendor\LaravelSSO\Facades\SSO;

// List all configured & enabled providers
SSO::enabledProviders();
// → ['google', 'github']

// Check if a provider is enabled
SSO::isEnabled('google');   // true
SSO::isEnabled('twitter');  // false

// Get config for a provider (throws if not enabled)
SSO::providerConfig('google');
// → ['client_id' => '...', 'client_secret' => '...', 'redirect' => '...']

// Get configured redirect URLs
SSO::redirectAfterLogin();   // '/dashboard'
SSO::redirectAfterLogout();  // '/login'

// Get the configured User model class
SSO::userModel(); // 'App\Models\User'
```

---

## Trait Helpers

Add `HasSSOProfile` to your `User` model to unlock these helpers:

```php
use YourVendor\LaravelSSO\Traits\HasSSOProfile;

class User extends Authenticatable
{
    use HasSSOProfile;
}
```

### Instance methods

```php
// Check if this user authenticated via SSO (any provider)
$user->isSSOUser();              // true | false

// Check if the user used a specific provider
$user->usesProvider('google');   // true | false
$user->usesProvider('github');   // false

// Get the SSO avatar, with an optional fallback URL
$user->ssoAvatar();                      // 'https://...' or ''
$user->ssoAvatar('/images/default.png'); // fallback if no avatar
```

### Query scopes

```php
// Get all users who logged in via Google
User::fromProvider('google')->get();

// Get all users who do NOT use SSO (have a password login)
User::nativeUsers()->get();

// Combine with other scopes
User::fromProvider('azure')->where('created_at', '>', now()->subDays(7))->count();
```

---

## Publishing Assets

```bash
# Publish config file only
php artisan vendor:publish --tag=sso-config

# Publish views only (to customize login buttons)
php artisan vendor:publish --tag=sso-views

# Publish migrations only
php artisan vendor:publish --tag=sso-migrations

# Publish everything at once
php artisan sso:publish

# Force overwrite existing published files
php artisan sso:publish --force
```

---

## Testing

```bash
composer test
```

---

## Build the Package from Scratch

Follow these steps to build this package yourself from zero.

### Step 1 — Create the directory structure

```bash
mkdir -p packages/yourvendor/laravel-sso/{src/{Commands,Contracts,Events,Exceptions,Http/{Controllers,Middleware},Traits},config,database/migrations,resources/views/auth,routes,tests/{Feature,Unit}}
```

### Step 2 — Create `composer.json`

```json
{
    "name": "yourvendor/laravel-sso",
    "description": "A simple and flexible SSO package for Laravel using OAuth2/OpenID Connect.",
    "version": "1.0.0",
    "type": "library",
    "keywords": ["laravel", "sso", "oauth2", "openid", "authentication"],
    "license": "MIT",
    "authors": [
        {
            "name": "Your Name",
            "email": "you@example.com"
        }
    ],
    "require": {
        "php": "^8.1",
        "illuminate/support": "^10.0|^11.0",
        "laravel/socialite": "^5.0"
    },
    "require-dev": {
        "orchestra/testbench": "^8.0|^9.0",
        "phpunit/phpunit": "^10.0",
        "mockery/mockery": "^1.6"
    },
    "autoload": {
        "psr-4": {
            "YourVendor\\LaravelSSO\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "YourVendor\\LaravelSSO\\Tests\\": "tests/"
        }
    },
    "extra": {
        "laravel": {
            "providers": [
                "YourVendor\\LaravelSSO\\SSOServiceProvider"
            ],
            "aliases": {
                "SSO": "YourVendor\\LaravelSSO\\Facades\\SSO"
            }
        }
    },
    "minimum-stability": "stable",
    "prefer-stable": true
}
```

> The `extra.laravel` block enables **auto-discovery** — Laravel registers the provider and facade automatically on `composer require`.

### Step 3 — Create `src/SSOServiceProvider.php`

```php
<?php

namespace YourVendor\LaravelSSO;

use Illuminate\Support\ServiceProvider;
use YourVendor\LaravelSSO\Commands\InstallSSOCommand;
use YourVendor\LaravelSSO\Commands\PublishSSOCommand;
use YourVendor\LaravelSSO\Contracts\SSOManagerContract;

class SSOServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/sso.php', 'sso');

        $this->app->singleton(SSOManagerContract::class, function ($app) {
            return new SSOManager($app['config']['sso']);
        });

        $this->app->alias(SSOManagerContract::class, 'sso');
    }

    public function boot(): void
    {
        $this->registerRoutes();
        $this->registerViews();
        $this->registerMigrations();
        $this->registerPublishables();
        $this->registerCommands();
        $this->registerEventListeners();
    }

    protected function registerRoutes(): void
    {
        if (config('sso.register_routes', true)) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/sso.php');
        }
    }

    protected function registerViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'sso');
    }

    protected function registerMigrations(): void
    {
        if (config('sso.run_migrations', true)) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }
    }

    protected function registerPublishables(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../config/sso.php' => config_path('sso.php'),
        ], 'sso-config');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/sso'),
        ], 'sso-views');

        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'sso-migrations');

        $this->publishes([
            __DIR__ . '/../config/sso.php'     => config_path('sso.php'),
            __DIR__ . '/../resources/views'     => resource_path('views/vendor/sso'),
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'sso');
    }

    protected function registerCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            InstallSSOCommand::class,
            PublishSSOCommand::class,
        ]);
    }

    protected function registerEventListeners(): void
    {
        foreach (config('sso.listeners', []) as $event => $listener) {
            $this->app['events']->listen($event, $listener);
        }
    }
}
```

### Step 4 — Create `src/Contracts/SSOManagerContract.php`

```php
<?php

namespace YourVendor\LaravelSSO\Contracts;

interface SSOManagerContract
{
    public function enabledProviders(): array;
    public function isEnabled(string $provider): bool;
    public function providerConfig(string $provider): array;
    public function redirectAfterLogin(): string;
    public function redirectAfterLogout(): string;
    public function userModel(): string;
}
```

### Step 5 — Create `src/SSOManager.php`

```php
<?php

namespace YourVendor\LaravelSSO;

use YourVendor\LaravelSSO\Contracts\SSOManagerContract;
use YourVendor\LaravelSSO\Exceptions\ProviderNotConfiguredException;

class SSOManager implements SSOManagerContract
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function enabledProviders(): array
    {
        return array_keys(array_filter(
            $this->config['providers'] ?? [],
            fn ($p) => ! empty($p['client_id'])
        ));
    }

    public function isEnabled(string $provider): bool
    {
        return in_array($provider, $this->enabledProviders());
    }

    public function providerConfig(string $provider): array
    {
        if (! $this->isEnabled($provider)) {
            throw new ProviderNotConfiguredException(
                "SSO provider [{$provider}] is not configured or not enabled."
            );
        }

        return $this->config['providers'][$provider];
    }

    public function redirectAfterLogin(): string
    {
        return $this->config['redirect_after_login'] ?? '/dashboard';
    }

    public function redirectAfterLogout(): string
    {
        return $this->config['redirect_after_logout'] ?? '/login';
    }

    public function userModel(): string
    {
        return $this->config['user_model'] ?? \App\Models\User::class;
    }
}
```

### Step 6 — Create `src/Facades/SSO.php`

```php
<?php

namespace YourVendor\LaravelSSO\Facades;

use Illuminate\Support\Facades\Facade;
use YourVendor\LaravelSSO\Contracts\SSOManagerContract;

/**
 * @method static array  enabledProviders()
 * @method static bool   isEnabled(string $provider)
 * @method static array  providerConfig(string $provider)
 * @method static string redirectAfterLogin()
 * @method static string redirectAfterLogout()
 * @method static string userModel()
 *
 * @see \YourVendor\LaravelSSO\SSOManager
 */
class SSO extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SSOManagerContract::class;
    }
}
```

### Step 7 — Create `src/Http/Controllers/SSOController.php`

```php
<?php

namespace YourVendor\LaravelSSO\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use YourVendor\LaravelSSO\Events\SSOLoginFailed;
use YourVendor\LaravelSSO\Events\SSOLoginSucceeded;
use YourVendor\LaravelSSO\Events\SSOUserCreated;
use YourVendor\LaravelSSO\Facades\SSO;

class SSOController extends Controller
{
    /**
     * Step 1 — Redirect the user to the IdP.
     */
    public function redirect(string $provider): RedirectResponse
    {
        $this->ensureProviderIsEnabled($provider);

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Step 2 — Handle the IdP callback and log the user in.
     */
    public function callback(string $provider): RedirectResponse
    {
        $this->ensureProviderIsEnabled($provider);

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Throwable $e) {
            event(new SSOLoginFailed($provider, $e->getMessage()));

            return redirect(SSO::redirectAfterLogout())
                ->withErrors(['sso' => 'SSO authentication failed. Please try again.']);
        }

        $userModel = SSO::userModel();
        $isNewUser = false;
        $user      = $userModel::where('email', $socialUser->getEmail())->first();

        if (! $user) {
            $isNewUser = true;
            $user = $userModel::create([
                'name'              => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
                'email'             => $socialUser->getEmail(),
                'sso_provider'      => $provider,
                'sso_provider_id'   => $socialUser->getId(),
                'sso_token'         => $socialUser->token,
                'sso_avatar'        => $socialUser->getAvatar(),
                'email_verified_at' => now(),
                'password'          => null,
            ]);

            event(new SSOUserCreated($user, $provider));
        } else {
            $user->update([
                'sso_provider'    => $provider,
                'sso_provider_id' => $socialUser->getId(),
                'sso_token'       => $socialUser->token,
                'sso_avatar'      => $socialUser->getAvatar(),
            ]);
        }

        Auth::login($user, remember: true);

        event(new SSOLoginSucceeded($user, $provider, $isNewUser));

        return redirect()->intended(SSO::redirectAfterLogin());
    }

    /**
     * Step 3 — Log the user out (clear local session only).
     */
    public function logout(): RedirectResponse
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect(SSO::redirectAfterLogout());
    }

    protected function ensureProviderIsEnabled(string $provider): void
    {
        if (! SSO::isEnabled($provider)) {
            abort(404, "SSO provider [{$provider}] is not enabled.");
        }
    }
}
```

### Step 8 — Create `src/Http/Middleware/EnsureSSOUser.php`

```php
<?php

namespace YourVendor\LaravelSSO\Http\Middleware;

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
```

### Step 9 — Create `src/Traits/HasSSOProfile.php`

```php
<?php

namespace YourVendor\LaravelSSO\Traits;

trait HasSSOProfile
{
    public function isSSOUser(): bool
    {
        return ! empty($this->sso_provider);
    }

    public function usesProvider(string $provider): bool
    {
        return $this->sso_provider === $provider;
    }

    public function ssoAvatar(string $default = ''): string
    {
        return $this->sso_avatar ?? $default;
    }

    public function scopeFromProvider($query, string $provider)
    {
        return $query->where('sso_provider', $provider);
    }

    public function scopeNativeUsers($query)
    {
        return $query->whereNull('sso_provider');
    }
}
```

### Step 10 — Create the three Event classes

**`src/Events/SSOLoginSucceeded.php`**
```php
<?php

namespace YourVendor\LaravelSSO\Events;

use Illuminate\Foundation\Auth\User;
use Illuminate\Queue\SerializesModels;

class SSOLoginSucceeded
{
    use SerializesModels;

    public function __construct(
        public readonly User   $user,
        public readonly string $provider,
        public readonly bool   $isNewUser = false,
    ) {}
}
```

**`src/Events/SSOLoginFailed.php`**
```php
<?php

namespace YourVendor\LaravelSSO\Events;

class SSOLoginFailed
{
    public function __construct(
        public readonly string $provider,
        public readonly string $reason,
    ) {}
}
```

**`src/Events/SSOUserCreated.php`**
```php
<?php

namespace YourVendor\LaravelSSO\Events;

use Illuminate\Foundation\Auth\User;
use Illuminate\Queue\SerializesModels;

class SSOUserCreated
{
    use SerializesModels;

    public function __construct(
        public readonly User   $user,
        public readonly string $provider,
    ) {}
}
```

### Step 11 — Create `src/Exceptions/ProviderNotConfiguredException.php`

```php
<?php

namespace YourVendor\LaravelSSO\Exceptions;

use RuntimeException;

class ProviderNotConfiguredException extends RuntimeException {}
```

### Step 12 — Create the Artisan commands

**`src/Commands/InstallSSOCommand.php`**
```php
<?php

namespace YourVendor\LaravelSSO\Commands;

use Illuminate\Console\Command;

class InstallSSOCommand extends Command
{
    protected $signature   = 'sso:install';
    protected $description = 'Install and configure the Laravel SSO package';

    public function handle(): int
    {
        $this->info('🔐 Installing Laravel SSO Package...');

        $this->call('vendor:publish', ['--tag' => 'sso-config', '--force' => false]);
        $this->info('✅ Config published → config/sso.php');

        $this->call('vendor:publish', ['--tag' => 'sso-migrations', '--force' => false]);
        $this->info('✅ Migrations published');

        if ($this->confirm('Publish views for customization?', false)) {
            $this->call('vendor:publish', ['--tag' => 'sso-views']);
            $this->info('✅ Views → resources/views/vendor/sso/');
        }

        if ($this->confirm('Run migrations now?', true)) {
            $this->call('migrate');
        }

        $this->info('🎉 Laravel SSO installed successfully!');
        $this->line('');
        $this->comment('Next steps:');
        $this->line('  1. Add SSO credentials to .env');
        $this->line('  2. Add HasSSOProfile trait to your User model');
        $this->line('  3. Enable providers in config/sso.php');

        return self::SUCCESS;
    }
}
```

**`src/Commands/PublishSSOCommand.php`**
```php
<?php

namespace YourVendor\LaravelSSO\Commands;

use Illuminate\Console\Command;

class PublishSSOCommand extends Command
{
    protected $signature   = 'sso:publish {--force : Overwrite existing files}';
    protected $description = 'Publish all SSO package assets';

    public function handle(): int
    {
        $this->call('vendor:publish', [
            '--tag'   => 'sso',
            '--force' => $this->option('force'),
        ]);

        $this->info('✅ All SSO assets published.');

        return self::SUCCESS;
    }
}
```

### Step 13 — Create `routes/sso.php`

```php
<?php

use Illuminate\Support\Facades\Route;
use YourVendor\LaravelSSO\Http\Controllers\SSOController;

Route::middleware(['web', 'throttle:20,1'])
    ->prefix('sso')
    ->name('sso.')
    ->group(function () {
        Route::get('/{provider}/redirect', [SSOController::class, 'redirect'])->name('redirect');
        Route::get('/{provider}/callback', [SSOController::class, 'callback'])->name('callback');
        Route::post('/logout',             [SSOController::class, 'logout'])->name('logout');
    });
```

### Step 14 — Create the migration

**`database/migrations/2024_01_01_000000_add_sso_columns_to_users_table.php`**
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'sso_provider')) {
                $table->string('sso_provider')->nullable()->after('email');
            }
            if (! Schema::hasColumn('users', 'sso_provider_id')) {
                $table->string('sso_provider_id')->nullable()->unique()->after('sso_provider');
            }
            if (! Schema::hasColumn('users', 'sso_token')) {
                $table->text('sso_token')->nullable()->after('sso_provider_id');
            }
            if (! Schema::hasColumn('users', 'sso_avatar')) {
                $table->string('sso_avatar')->nullable()->after('sso_token');
            }

            $table->string('password')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['sso_provider', 'sso_provider_id', 'sso_token', 'sso_avatar']);
        });
    }
};
```

### Step 15 — Create `resources/views/auth/login.blade.php`

```blade
<div class="sso-buttons">
    @foreach (config('sso.providers') as $provider => $cfg)
        @if (!empty($cfg['client_id']))
            <a href="{{ route('sso.redirect', $provider) }}"
               class="sso-btn sso-btn--{{ $provider }}">
                Sign in with {{ ucfirst($provider) }}
            </a>
        @endif
    @endforeach
</div>
```

### Step 16 — Create `phpunit.xml`

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/10.0/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>
    <source>
        <include>
            <directory>src</directory>
        </include>
    </source>
</phpunit>
```

### Step 17 — Create Tests

**`tests/Unit/SSOManagerTest.php`**
```php
<?php

namespace YourVendor\LaravelSSO\Tests\Unit;

use Orchestra\Testbench\TestCase;
use YourVendor\LaravelSSO\Exceptions\ProviderNotConfiguredException;
use YourVendor\LaravelSSO\SSOManager;
use YourVendor\LaravelSSO\SSOServiceProvider;

class SSOManagerTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [SSOServiceProvider::class];
    }

    private function makeManager(array $config = []): SSOManager
    {
        return new SSOManager(array_merge([
            'providers' => [
                'google' => ['client_id' => 'abc', 'client_secret' => 'xyz', 'redirect' => '/'],
                'github' => ['client_id' => '',    'client_secret' => '',    'redirect' => '/'],
            ],
        ], $config));
    }

    /** @test */
    public function it_returns_only_enabled_providers(): void
    {
        $this->assertEquals(['google'], $this->makeManager()->enabledProviders());
    }

    /** @test */
    public function it_reports_enabled_state_correctly(): void
    {
        $manager = $this->makeManager();

        $this->assertTrue($manager->isEnabled('google'));
        $this->assertFalse($manager->isEnabled('github'));
        $this->assertFalse($manager->isEnabled('azure'));
    }

    /** @test */
    public function it_throws_for_unconfigured_provider(): void
    {
        $this->expectException(ProviderNotConfiguredException::class);
        $this->makeManager()->providerConfig('github');
    }

    /** @test */
    public function it_returns_correct_redirect_urls(): void
    {
        $manager = $this->makeManager([
            'redirect_after_login'  => '/home',
            'redirect_after_logout' => '/bye',
        ]);

        $this->assertEquals('/home', $manager->redirectAfterLogin());
        $this->assertEquals('/bye',  $manager->redirectAfterLogout());
    }
}
```

**`tests/Feature/SSOControllerTest.php`**
```php
<?php

namespace YourVendor\LaravelSSO\Tests\Feature;

use Orchestra\Testbench\TestCase;
use YourVendor\LaravelSSO\SSOServiceProvider;

class SSOControllerTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [SSOServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('sso.providers', [
            'google' => [
                'client_id'     => 'fake-client-id',
                'client_secret' => 'fake-client-secret',
                'redirect'      => '/sso/google/callback',
            ],
        ]);
    }

    /** @test */
    public function it_redirects_to_provider(): void
    {
        \Laravel\Socialite\Facades\Socialite::shouldReceive('driver->redirect')
            ->once()
            ->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));

        $response = $this->get('/sso/google/redirect');
        $response->assertRedirect();
    }

    /** @test */
    public function it_returns_404_for_disabled_provider(): void
    {
        $response = $this->get('/sso/notreal/redirect');
        $response->assertNotFound();
    }

    /** @test */
    public function it_redirects_with_error_on_callback_failure(): void
    {
        \Laravel\Socialite\Facades\Socialite::shouldReceive('driver->user')
            ->once()
            ->andThrow(new \Exception('Invalid state'));

        $response = $this->get('/sso/google/callback');
        $response->assertRedirect();
        $response->assertSessionHasErrors('sso');
    }
}
```

---

## Publish to Packagist

Once the package is complete, publish it so other developers can install it with `composer require`.

### Step 1 — Push to GitHub

```bash
cd packages/yourvendor/laravel-sso

git init
git add .
git commit -m "feat: initial release v1.0.0"

# Create the repo on GitHub first, then:
git remote add origin https://github.com/yourvendor/laravel-sso.git
git branch -M main
git push -u origin main

# Tag the release
git tag v1.0.0
git push origin v1.0.0
```

### Step 2 — Submit to Packagist

1. Go to [packagist.org](https://packagist.org)
2. Click **Submit**
3. Paste your GitHub repository URL: `https://github.com/yourvendor/laravel-sso`
4. Click **Check** → **Submit**

### Step 3 — Set up auto-update webhook

In your GitHub repo: **Settings → Webhooks → Add webhook**

- Payload URL: `https://packagist.org/api/github?username=yourpackagistusername`
- Content type: `application/json`
- Secret: *(your Packagist API token)*

### Step 4 — Others install with

```bash
composer require yourvendor/laravel-sso
php artisan sso:install
```

---

## License

MIT