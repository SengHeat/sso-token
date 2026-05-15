<?php

namespace SengHeat\SsoToken\Facades;

use Illuminate\Support\Facades\Facade;
use SengHeat\SsoToken\Services\SsoManager;
use SengHeat\SsoToken\SsoUser;

/**
 * @method static SsoUser|null              user()
 * @method static int|string|null           id()
 * @method static bool                      check()
 * @method static bool                      guest()
 * @method static array|null                payload()
 * @method static mixed                     claim(string $key, mixed $default = null)
 * @method static string|null               token()
 * @method static void                      setPayload(array $payload, ?string $rawToken = null)
 * @method static void                      clear()
 *
 * @see SsoManager
 */
class SsoAuth extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SsoManager::class;
    }
}
