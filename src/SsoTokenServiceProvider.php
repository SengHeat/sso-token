<?php

namespace SengHeat\SsoToken;

use Illuminate\Support\ServiceProvider;
use SengHeat\SsoToken\Console\InstallCommand;
use SengHeat\SsoToken\Middleware\VerifyToken;
use SengHeat\SsoToken\Services\TokenService;

class SsoTokenServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/sso.php', 'sso');

        $this->app->singleton(TokenService::class, fn () => new TokenService());
    }

    public function boot(): void
    {
        // Publish config
        $this->publishes([
            __DIR__ . '/../config/sso.php' => config_path('sso.php'),
        ], 'sso-config');

        // Register middleware alias
        $router = $this->app['router'];
        $router->aliasMiddleware('sso.verify', VerifyToken::class);

        // Register artisan command
        if ($this->app->runningInConsole()) {
            $this->commands([InstallCommand::class]);
        }
    }
}
