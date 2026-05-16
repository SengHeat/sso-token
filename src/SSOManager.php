<?php

namespace SengHeat\LaravelSso;

use SengHeat\LaravelSso\Contracts\SSOManagerContract;
use SengHeat\LaravelSso\Exceptions\ProviderNotConfiguredException;

class SSOManager implements SSOManagerContract
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function enabledProviders(): array
    {
        return array_keys(array_filter(
            $this->config['providers'] ?? [],
            fn ($p) => ! empty($p['client_id'])
        ));
    }

    public function isEnabled(string $provider): bool
    {
        return in_array($provider, $this->enabledProviders());
    }

    public function providerConfig(string $provider): array
    {
        if (! $this->isEnabled($provider)) {
            throw new ProviderNotConfiguredException(
                "SSO provider [{$provider}] is not configured or not enabled."
            );
        }

        return $this->config['providers'][$provider];
    }

    public function redirectAfterLogin(): string
    {
        return $this->config['redirect_after_login'] ?? '/dashboard';
    }

    public function redirectAfterLogout(): string
    {
        return $this->config['redirect_after_logout'] ?? '/login';
    }

    public function userModel(): string
    {
        return $this->config['user_model'] ?? \App\Models\User::class;
    }
}
