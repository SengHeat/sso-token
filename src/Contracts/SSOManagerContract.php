<?php

namespace SengHeat\LaravelSso\Contracts;

interface SSOManagerContract
{
    public function enabledProviders(): array;
    public function isEnabled(string $provider): bool;
    public function providerConfig(string $provider): array;
    public function redirectAfterLogin(): string;
    public function redirectAfterLogout(): string;
    public function userModel(): string;
}
