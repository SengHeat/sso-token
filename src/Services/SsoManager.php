<?php

namespace SengHeat\SsoToken\Services;

use SengHeat\SsoToken\SsoUser;

/**
 * Passport-style authentication manager for SSO tokens.
 *
 * Populated by the VerifyToken middleware after a successful verification.
 * Resolved via the `ssoAuth()` helper or the `SsoAuth` facade.
 *
 * @example
 *   ssoAuth()->user()          // SsoUser|null
 *   ssoAuth()->id()            // sub claim value or null
 *   ssoAuth()->check()         // bool — is authenticated?
 *   ssoAuth()->guest()         // bool — not authenticated?
 *   ssoAuth()->payload()       // full claims array or null
 *   ssoAuth()->claim('role')   // single claim or null
 *   ssoAuth()->token()         // raw Bearer token string or null
 */
class SsoManager
{
    private ?array $payload = null;

    private ?string $rawToken = null;

    /** Called by VerifyToken middleware after successful verification. */
    public function setPayload(array $payload, ?string $rawToken = null): void
    {
        $this->payload   = $payload;
        $this->rawToken  = $rawToken;
    }

    /** Returns the authenticated user as an SsoUser object, or null. */
    public function user(): ?SsoUser
    {
        return $this->payload !== null ? new SsoUser($this->payload) : null;
    }

    /** Returns the user's ID (the `sub` claim), or null. */
    public function id(): int|string|null
    {
        return $this->payload['sub'] ?? null;
    }

    /** Returns true when a verified token is present. */
    public function check(): bool
    {
        return $this->payload !== null;
    }

    /** Returns true when no verified token is present. */
    public function guest(): bool
    {
        return $this->payload === null;
    }

    /** Returns the full decoded payload array, or null. */
    public function payload(): ?array
    {
        return $this->payload;
    }

    /** Returns a single claim value, or $default if missing. */
    public function claim(string $key, mixed $default = null): mixed
    {
        return $this->payload[$key] ?? $default;
    }

    /** Returns the raw Bearer token string, or null. */
    public function token(): ?string
    {
        return $this->rawToken;
    }

    /** Clears the current authentication state (useful for testing). */
    public function clear(): void
    {
        $this->payload  = null;
        $this->rawToken = null;
    }
}
