<?php

namespace SengHeat\LaravelSso\Events;

use Illuminate\Foundation\Auth\User;
use Illuminate\Queue\SerializesModels;

class SSOLoginSucceeded
{
    use SerializesModels;

    public function __construct(
        public readonly User   $user,
        public readonly string $provider,
        public readonly bool   $isNewUser = false,
    ) {}
}
