<?php

use SengHeat\SsoToken\Services\SsoManager;

if (! function_exists('ssoAuth')) {
    /**
     * Return the SsoManager singleton — the Passport-style SSO auth helper.
     *
     * @example
     *   ssoAuth()->user()       // SsoUser|null
     *   ssoAuth()->id()         // user ID (sub claim) or null
     *   ssoAuth()->check()      // bool
     *   ssoAuth()->guest()      // bool
     *   ssoAuth()->claim('role') // single claim value or null
     *   ssoAuth()->token()      // raw Bearer token or null
     */
    function ssoAuth(): SsoManager
    {
        return app(SsoManager::class);
    }
}
