<?php

namespace SengHeat\SsoToken;

use Illuminate\Support\ServiceProvider;
use SengHeat\SsoToken\Console\InstallCommand;
use SengHeat\SsoToken\Middleware\VerifyToken;
use SengHeat\SsoToken\Services\SsoManager;
use SengHeat\SsoToken\Services\TokenService;

class SsoTokenServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/sso.php', 'sso');

        $this->app->singleton(TokenService::class, function () {
            return new TokenService(config('sso'));
        });

        // Singleton so the middleware-populated state survives the request lifecycle
        $this->app->singleton(SsoManager::class, fn () => new SsoManager());
    }

    public function boot(): void
    {
        $this->registerPublishing();
        $this->registerMiddleware();
        $this->registerCommands();
    }

    private function registerPublishing(): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../config/sso.php' => config_path('sso.php'),
        ], 'sso-config');

        $this->publishes([
            __DIR__ . '/../stubs/.env.sso' => base_path('.env.sso.example'),
        ], 'sso-env');
    }

    private function registerMiddleware(): void
    {
        $this->app['router']->aliasMiddleware('sso.verify', VerifyToken::class);
    }

    private function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([InstallCommand::class]);
        }
    }

    public function provides(): array
    {
        return [TokenService::class, SsoManager::class];
    }
}