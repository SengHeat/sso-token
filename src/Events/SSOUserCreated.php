<?php

namespace SengHeat\LaravelSso\Events;

use Illuminate\Foundation\Auth\User;
use Illuminate\Queue\SerializesModels;

class SSOUserCreated
{
    use SerializesModels;

    public function __construct(
        public readonly User   $user,
        public readonly string $provider,
    ) {}
}
