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
    private static $signPrivateKey = null;
    private static $signPublicKey  = null;
    private static $encPublicKey   = null;
    private static $encPrivateKey  = null;

    private function algorithmManager(): AlgorithmManager
    {
        return new AlgorithmManager([
            new RS256(),
            new RSAOAEP256(),
            new A256GCM(),
        ]);
    }

    private function getSignPrivateKey()
    {
        if (!static::$signPrivateKey) {
            static::$signPrivateKey = JWKFactory::createFromKeyFile(
                config('sso.sign_private'),
                null,
                []
            );
        }

        return static::$signPrivateKey;
    }

    private function getSignPublicKey()
    {
        if (!static::$signPublicKey) {
            static::$signPublicKey = JWKFactory::createFromKeyFile(
                config('sso.sign_public'),
                null,
                []
            );
        }

        return static::$signPublicKey;
    }

    private function getEncPublicKey()
    {
        if (!static::$encPublicKey) {
            static::$encPublicKey = JWKFactory::createFromKeyFile(
                config('sso.enc_public'),
                null,
                []
            );
        }

        return static::$encPublicKey;
    }

    private function getEncPrivateKey()
    {
        if (!static::$encPrivateKey) {
            static::$encPrivateKey = JWKFactory::createFromKeyFile(
                config('sso.enc_private'),
                null,
                []
            );
        }

        return static::$encPrivateKey;
    }

    /**
     * Issue a signed + encrypted JWE token.
     *
     * @param  array<string, mixed>  $claims
     *
     * @throws RuntimeException when called in verify mode
     */
    public function issue(array $claims): string
    {
        if (config('sso.mode') === 'verify') {
            throw new RuntimeException('Cannot issue tokens in verify mode');
        }

        $payload = array_merge($claims, [
            'iss' => config('sso.issuer'),
            'iat' => time(),
            'nbf' => time(),
            'exp' => time() + (config('sso.ttl', 15) * 60),
            'jti' => bin2hex(random_bytes(16)),
        ]);

        $algorithmManager = $this->algorithmManager();

        // Step 1 — Sign with RS256
        $jws = (new JWSBuilder($algorithmManager))
            ->create()
            ->withPayload(json_encode($payload))
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
     * Verify a JWE token and return its payload.
     *
     * @return array<string, mixed>
     *
     * @throws RuntimeException on any verification failure
     */
    public function verify(string $token): array
    {
        $algorithmManager = $this->algorithmManager();

        // Step 1 — Decrypt JWE
        $jweSerializerManager = new JWESerializerManager([new JWESerializer()]);
        $jwe                  = $jweSerializerManager->unserialize($token);
        $jweDecrypter         = new JWEDecrypter($algorithmManager);

        $success = $jweDecrypter->decryptUsingKey($jwe, $this->getEncPrivateKey(), 0);
        if (!$success) {
            throw new RuntimeException('Token decryption failed');
        }

        $innerJWT = $jwe->getPayload();

        // Step 2 — Verify JWS signature
        $jws         = (new JWSSerializer())->unserialize($innerJWT);
        $jwsVerifier = new JWSVerifier($algorithmManager);

        if (!$jwsVerifier->verifyWithKey($jws, $this->getSignPublicKey(), 0)) {
            throw new RuntimeException('Token signature invalid');
        }

        /** @var array<string, mixed> $payload */
        $payload = json_decode($jws->getPayload(), true);

        // Step 3 — Validate claims
        if (($payload['exp'] ?? 0) < time()) {
            throw new RuntimeException('Token expired');
        }

        if (($payload['iss'] ?? '') !== config('sso.issuer')) {
            throw new RuntimeException('Token issuer invalid');
        }

        // Step 4 — Check Redis blocklist
        if (Redis::exists("blocklist:{$payload['jti']}")) {
            throw new RuntimeException('Token revoked');
        }

        return $payload;
    }
}
