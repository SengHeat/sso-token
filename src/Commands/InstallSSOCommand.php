<?php

namespace SengHeat\LaravelSso\Commands;

use Illuminate\Console\Command;

class InstallSSOCommand extends Command
{
    protected $signature   = 'sso:install';
    protected $description = 'Install and configure the Laravel SSO package';

    public function handle(): int
    {
        $this->info('Installing Laravel SSO Package...');

        $this->call('vendor:publish', ['--tag' => 'sso-config', '--force' => false]);
        $this->info('Config published → config/sso.php');

        $this->call('vendor:publish', ['--tag' => 'sso-migrations', '--force' => false]);
        $this->info('Migrations published');

        if ($this->confirm('Publish views for customization?', false)) {
            $this->call('vendor:publish', ['--tag' => 'sso-views']);
            $this->info('Views → resources/views/vendor/sso/');
        }

        if ($this->confirm('Run migrations now?', true)) {
            $this->call('migrate');
        }

        $this->info('Laravel SSO installed successfully!');
        $this->newLine();
        $this->comment('Next steps:');
        $this->line('  1. Add SSO credentials to .env');
        $this->line('  2. Add HasSSOProfile trait to your User model');
        $this->line('  3. Enable providers in config/sso.php');

        return self::SUCCESS;
    }
}
