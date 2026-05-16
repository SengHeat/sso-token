<?php

namespace SengHeat\LaravelSso\Traits;

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
