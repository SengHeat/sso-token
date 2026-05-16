<?php

namespace SengHeat\LaravelSso\Traits;

use Illuminate\Support\Str;

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

    public function generateApiToken(): string
    {
        $plainToken = Str::random(80);

        $this->forceFill([
            'api_token' => hash('sha256', $plainToken),
        ])->save();

        return $plainToken;
    }

    public function revokeApiToken(): void
    {
        $this->forceFill(['api_token' => null])->save();
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
