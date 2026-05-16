<?php

namespace SengHeat\LaravelSso\Events;

class SSOLoginFailed
{
    public function __construct(
        public readonly string $provider,
        public readonly string $reason,
    ) {}
}
