<?php

namespace SengHeat\SsoToken\Services;

use Illuminate\Support\Facades\Redis;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Encryption\Algorithm\ContentEncryption\A256GCM;
use Jose\Component\Encryption\Algorithm\KeyEncryption\RSAOAEP256;
use Jose\Component\Encryption\JWEBuilder;
use Jose\Component\Encryption\JWEDecrypter;
use Jose\Component\Encryption\Serializer\CompactSerializer as JWESerializer;
use Jose\Component\Encryption\Serializer\JWESerializerManager;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\Algorithm\RS256;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\Serializer\CompactSerializer as JWSSerializer;
use RuntimeException;

class TokenService
{
    private static array $keyCache = [];

    private array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config ?: config('sso', []);
    }

    private function algorithmManager(): AlgorithmManager
    {
        return new AlgorithmManager([
            new RS256(),
            new RSAOAEP256(),
            new A256GCM(),
        ]);
    }

    private function loadKey(string $path): mixed
    {
        if (isset(static::$keyCache[$path])) {
            return static::$keyCache[$path];
        }

        if (!file_exists($path)) {
            throw new RuntimeException("SSO key file not found: {$path}");
        }

        return static::$keyCache[$path] = JWKFactory::createFromKeyFile($path, null, []);
    }

    private function getSignPrivateKey(): mixed
    {
        $this->requireMode('issue', 'sign_private');
        return $this->loadKey($this->config['sign_private']);
    }

    private function getSignPublicKey(): mixed
    {
        return $this->loadKey($this->config['sign_public']);
    }

    private function getEncPublicKey(): mixed
    {
        $this->requireMode('issue', 'enc_public');
        return $this->loadKey($this->config['enc_public']);
    }

    private function getEncPrivateKey(): mixed
    {
        return $this->loadKey($this->config['enc_private']);
    }

    private function requireMode(string $requiredMode, string $keyName): void
    {
        if (($this->config['mode'] ?? 'verify') !== $requiredMode) {
            throw new RuntimeException(
                "Key [{$keyName}] is not available in [{$this->config['mode']}] mode."
            );
        }
    }

    /**
     * Issue a signed + encrypted JWE token.
     *
     * @param  array<string, mixed>  $claims
     * @throws RuntimeException
     */
    public function issue(array $claims): string
    {
        if (($this->config['mode'] ?? 'verify') !== 'issue') {
            throw new RuntimeException('Cannot issue tokens in verify mode.');
        }

        $payload = array_merge($claims, [
            'iss' => $this->config['issuer'],
            'iat' => time(),
            'nbf' => time(),
            'exp' => time() + (($this->config['ttl'] ?? 15) * 60),
            'jti' => bin2hex(random_bytes(16)),
        ]);

        $algorithmManager = $this->algorithmManager();

        // Step 1 — Sign with RS256
        $jws = (new JWSBuilder($algorithmManager))
            ->create()
            ->withPayload(json_encode($payload, JSON_THROW_ON_ERROR))
            ->addSignature($this->getSignPrivateKey(), [
                'alg' => 'RS256',
                'typ' => 'JWT',
                'kid' => 'sign-v1',
            ])
            ->build();

        $signedToken = (new JWSSerializer())->serialize($jws, 0);

        // Step 2 — Encrypt with RSA-OAEP-256 + A256GCM
        $jwe = (new JWEBuilder($algorithmManager))
            ->create()
            ->withPayload($signedToken)
            ->withSharedProtectedHeader([
                'alg' => 'RSA-OAEP-256',
                'enc' => 'A256GCM',
                'cty' => 'JWT',
                'kid' => 'enc-v1',
            ])
            ->addRecipient($this->getEncPublicKey())
            ->build();

        return (new JWESerializer())->serialize($jwe, 0);
    }

    /**
     * Verify and decrypt a JWE token — returns payload.
     *
     * @return array<string, mixed>
     * @throws RuntimeException
     */
    public function verify(string $token): array
    {
        $algorithmManager = $this->algorithmManager();

        // Step 1 — Decrypt JWE
        $jwe     = (new JWESerializerManager([new JWESerializer()]))->unserialize($token);
        $success = (new JWEDecrypter($algorithmManager))
            ->decryptUsingKey($jwe, $this->getEncPrivateKey(), 0);

        if (!$success) {
            throw new RuntimeException('Token decryption failed.');
        }

        // Step 2 — Verify JWS signature
        $jws     = (new JWSSerializer())->unserialize($jwe->getPayload());
        $isValid = (new JWSVerifier($algorithmManager))
            ->verifyWithKey($jws, $this->getSignPublicKey(), 0);

        if (!$isValid) {
            throw new RuntimeException('Token signature invalid.');
        }

        /** @var array<string, mixed> $payload */
        $payload = json_decode($jws->getPayload(), true, 512, JSON_THROW_ON_ERROR);

        // Step 3 — Validate claims
        if (($payload['exp'] ?? 0) < time()) {
            throw new RuntimeException('Token expired.');
        }

        if (($payload['iss'] ?? '') !== ($this->config['issuer'] ?? '')) {
            throw new RuntimeException('Token issuer invalid.');
        }

        // Step 4 — Check Redis blocklist
        if (Redis::exists("blocklist:{$payload['jti']}")) {
            throw new RuntimeException('Token has been revoked.');
        }

        return $payload;
    }

    /**
     * Revoke a token by JTI until its expiry.
     */
    public function revoke(array $payload): void
    {
        $ttl = ($payload['exp'] ?? 0) - time();

        if ($ttl > 0) {
            Redis::setex("blocklist:{$payload['jti']}", $ttl, '1');
        }
    }

    /**
     * Clear the static key cache (useful for testing).
     */
    public static function flushKeyCache(): void
    {
        static::$keyCache = [];
    }
}