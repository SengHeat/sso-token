<?php

namespace SengHeat\LaravelSso\Commands;

use Illuminate\Console\Command;

class PublishSSOCommand extends Command
{
    protected $signature   = 'sso:publish {--force : Overwrite existing files}';
    protected $description = 'Publish all SSO package assets';

    public function handle(): int
    {
        $this->call('vendor:publish', [
            '--tag'   => 'sso',
            '--force' => $this->option('force'),
        ]);

        $this->info('All SSO assets published.');

        return self::SUCCESS;
    }
}
