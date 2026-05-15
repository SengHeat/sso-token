<?php

namespace SengHeat\SsoToken;

/**
 * Represents an authenticated SSO user backed by JWT payload claims.
 *
 * Access claims as properties:  $user->email, $user->role, …
 * The `id` property maps to the `sub` claim.
 */
class SsoUser
{
    public function __construct(private readonly array $attributes) {}

    /** Returns the user's identifier (the `sub` claim). */
    public function getId(): int|string|null
    {
        return $this->attributes['sub'] ?? null;
    }

    /** Returns a single claim value. */
    public function getAttribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    /** Returns every claim in the payload. */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * Magic property access — `$user->id` resolves to `sub`.
     */
    public function __get(string $name): mixed
    {
        if ($name === 'id') {
            return $this->attributes['sub'] ?? null;
        }

        return $this->attributes[$name] ?? null;
    }

    public function __isset(string $name): bool
    {
        if ($name === 'id') {
            return isset($this->attributes['sub']);
        }

        return isset($this->attributes[$name]);
    }
}
