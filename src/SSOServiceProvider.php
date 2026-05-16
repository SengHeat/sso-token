<?php

namespace SengHeat\LaravelSso;

use Illuminate\Support\ServiceProvider;
use SengHeat\LaravelSso\Commands\InstallSSOCommand;
use SengHeat\LaravelSso\Commands\PublishSSOCommand;
use SengHeat\LaravelSso\Contracts\SSOManagerContract;

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
            __DIR__ . '/../config/sso.php'      => config_path('sso.php'),
            __DIR__ . '/../resources/views'      => resource_path('views/vendor/sso'),
            __DIR__ . '/../database/migrations'  => database_path('migrations'),
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
        foreach (config('sso.listeners', []) as $event => $listeners) {
            foreach ((array) $listeners as $listener) {
                $this->app['events']->listen($event, $listener);
            }
        }
    }
}
