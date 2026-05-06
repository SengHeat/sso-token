<?php

namespace SengHeat\SsoToken\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature   = 'sso:install';
    protected $description = 'Install the SSO Token package — create key directory and append .env entries';

    public function handle(): int
    {
        // 1 — Create storage/keys/ directory
        $keysDir = storage_path('keys');
        if (!is_dir($keysDir)) {
            mkdir($keysDir, 0700, true);
            $this->info("Created directory: {$keysDir}");
        } else {
            $this->line("Directory already exists: {$keysDir}");
        }

        // 2 — Append .env block
        $envPath  = base_path('.env');
        $envBlock = <<<'ENV'


# ── SSO Token ────────────────────────────────────────────────
SSO_MODE=verify
SSO_AUTH_ISSUER=http://localhost:8000
SSO_TOKEN_TTL=15
SSO_REFRESH_TTL=7
SSO_SIGN_PUBLIC_KEY="${storage_path('keys/sign_public.pem')}"
SSO_SIGN_PRIVATE_KEY="${storage_path('keys/sign_private.pem')}"
SSO_ENC_PUBLIC_KEY="${storage_path('keys/enc_public.pem')}"
SSO_ENC_PRIVATE_KEY="${storage_path('keys/enc_private.pem')}"
# ────────────────────────────────────────────────────────────
ENV;

        if (file_exists($envPath)) {
            $existing = file_get_contents($envPath);

            if (str_contains($existing, 'SSO_MODE')) {
                $this->warn('.env already contains SSO_* entries — skipping append.');
            } else {
                file_put_contents($envPath, $existing . $envBlock);
                $this->info('Appended SSO_* entries to .env');
            }
        } else {
            $this->warn('.env file not found — skipping append.');
        }

        // 3 — Publish config
        $this->call('vendor:publish', ['--tag' => 'sso-config', '--force' => false]);

        // 4 — Print key copy instructions
        $this->newLine();
        $this->info('Next steps — copy your PEM keys into storage/keys/:');
        $this->newLine();
        $this->line('  <comment>Issue mode</comment> requires all four keys:');
        $this->line('    storage/keys/sign_private.pem   — RSA private key for signing (auth service only)');
        $this->line('    storage/keys/sign_public.pem    — RSA public key for signature verification');
        $this->line('    storage/keys/enc_public.pem     — RSA public key for encryption (auth service only)');
        $this->line('    storage/keys/enc_private.pem    — RSA private key for decryption');
        $this->newLine();
        $this->line('  <comment>Verify mode</comment> requires only:');
        $this->line('    storage/keys/sign_public.pem');
        $this->line('    storage/keys/enc_private.pem');
        $this->newLine();
        $this->line('  Set <comment>SSO_MODE=issue</comment> in .env for the auth/issuing service.');
        $this->line('  Set <comment>SSO_MODE=verify</comment> in .env for all consuming services.');
        $this->newLine();

        $this->info('SSO Token installed successfully.');

        return self::SUCCESS;
    }
}
