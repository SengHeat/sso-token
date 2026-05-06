<?php

namespace SengHeat\SsoToken\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature   = 'sso:install';
    protected $description = 'Install the laravel-sso-token package';

    public function handle(): void
    {
        // Create keys directory
        $keysPath = storage_path('keys');
        if (!is_dir($keysPath)) {
            mkdir($keysPath, 0700, true);
            $this->info('Created storage/keys/ directory.');
        }

        // Publish config
        $this->callSilent('vendor:publish', ['--tag' => 'sso-config']);
        $this->info('Published config/sso.php');

        // Append .env keys
        $envPath = base_path('.env');
        $envStub = "\n# SSO Token Package\n"
            . "SSO_MODE=verify\n"
            . "SSO_AUTH_ISSUER=http://localhost:8000\n"
            . "SSO_TOKEN_TTL=15\n"
            . "SSO_REFRESH_TTL=7\n"
            . "SSO_SIGN_PUBLIC_KEY=" . storage_path('keys/sign_public.pem') . "\n"
            . "SSO_SIGN_PRIVATE_KEY=" . storage_path('keys/sign_private.pem') . "\n"
            . "SSO_ENC_PUBLIC_KEY=" . storage_path('keys/enc_public.pem') . "\n"
            . "SSO_ENC_PRIVATE_KEY=" . storage_path('keys/enc_private.pem') . "\n";

        if (!str_contains(file_get_contents($envPath), 'SSO_MODE')) {
            file_put_contents($envPath, $envStub, FILE_APPEND);
            $this->info('Added SSO keys to .env');
        }

        $this->newLine();
        $this->info('✓ laravel-sso-token installed successfully.');
        $this->newLine();
        $this->table(
            ['Mode', 'Required Keys'],
            [
                ['verify', 'sign_public.pem + enc_private.pem'],
                ['issue',  'sign_private.pem + sign_public.pem + enc_private.pem + enc_public.pem'],
            ]
        );
        $this->newLine();
        $this->comment('Next steps:');
        $this->comment('  1. Copy your keys into storage/keys/');
        $this->comment('  2. Set SSO_MODE=verify or SSO_MODE=issue in .env');
        $this->comment('  3. Set SSO_AUTH_ISSUER to your auth server URL');
        $this->newLine();
    }
}