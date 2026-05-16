<?php

namespace SengHeat\LaravelSso\Facades;

use Illuminate\Support\Facades\Facade;
use SengHeat\LaravelSso\Contracts\SSOManagerContract;

/**
 * @method static array  enabledProviders()
 * @method static bool   isEnabled(string $provider)
 * @method static array  providerConfig(string $provider)
 * @method static string redirectAfterLogin()
 * @method static string redirectAfterLogout()
 * @method static string userModel()
 *
 * @see \SengHeat\LaravelSso\SSOManager
 */
class SSO extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SSOManagerContract::class;
    }
}
