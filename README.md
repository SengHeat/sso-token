# sso-token

A standalone Laravel package for issuing and verifying SSO tokens using nested JWE + JWS (RS256 signed, RSA-OAEP-256 + A256GCM encrypted).

---

## Installation

```bash
composer require sengheat/sso-token
```

Run the install command to scaffold the keys directory and append `.env` entries:

```bash
php artisan sso:install
```

Publish the config file:

```bash
php artisan vendor:publish --tag=sso-config
```

---

## Copy Keys

Place your RSA PEM keys in `storage/keys/`. Generate them with:

```bash
# Signing keypair
openssl genrsa -out storage/keys/sign_private.pem 4096
openssl rsa -in storage/keys/sign_private.pem -pubout -out storage/keys/sign_public.pem

# Encryption keypair
openssl genrsa -out storage/keys/enc_private.pem 4096
openssl rsa -in storage/keys/enc_private.pem -pubout -out storage/keys/enc_public.pem
```

| Mode     | Required keys                                                               |
|----------|-----------------------------------------------------------------------------|
| `issue`  | `sign_private.pem`, `sign_public.pem`, `enc_public.pem`, `enc_private.pem` |
| `verify` | `sign_public.pem`, `enc_private.pem`                                        |

Distribute only the public keys to consuming services. Never share private keys across service boundaries.

---

## Environment Variables

| Variable               | Default                         | Description                             |
|------------------------|---------------------------------|-----------------------------------------|
| `SSO_MODE`             | `verify`                        | `issue` or `verify`                     |
| `SSO_AUTH_ISSUER`      | `http://localhost:8000`         | Expected `iss` claim value              |
| `SSO_TOKEN_TTL`        | `15`                            | Access token TTL in minutes             |
| `SSO_REFRESH_TTL`      | `7`                             | Refresh token TTL in days               |
| `SSO_SIGN_PUBLIC_KEY`  | `storage/keys/sign_public.pem`  | Path to RSA public key for verification |
| `SSO_SIGN_PRIVATE_KEY` | `storage/keys/sign_private.pem` | Path to RSA private key for signing     |
| `SSO_ENC_PUBLIC_KEY`   | `storage/keys/enc_public.pem`   | Path to RSA public key for encryption   |
| `SSO_ENC_PRIVATE_KEY`  | `storage/keys/enc_private.pem`  | Path to RSA private key for decryption  |

---

## Usage

### Issue mode (auth service)

Set in `.env`:

```env
SSO_MODE=issue
SSO_AUTH_ISSUER=https://auth.example.com
SSO_TOKEN_TTL=15
```

Issue a token:

```php
use SengHeat\SsoToken\Services\TokenService;

$token = app(TokenService::class)->issue([
    'sub'   => $user->id,
    'email' => $user->email,
    'role'  => 'admin',
]);
```

Verify a token (also available in issue mode):

```php
$payload = app(TokenService::class)->verify($token);
// ['sub' => 1, 'email' => '...', 'role' => 'admin', 'iss' => '...', 'exp' => ..., ...]
```

Calling `issue()` in `verify` mode throws:

```
RuntimeException: Cannot issue tokens in verify mode
```

---

### Verify mode (consuming services)

Set in `.env`:

```env
SSO_MODE=verify
SSO_AUTH_ISSUER=https://auth.example.com
```

Only `sign_public.pem` and `enc_private.pem` are required.

---

## Middleware Usage

The `sso.verify` middleware alias is registered automatically.

### Protect individual routes

```php
// routes/api.php
use Illuminate\Support\Facades\Route;

Route::middleware('sso.verify')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/orders', [OrderController::class, 'store']);
});
```

### Protect a single route

```php
Route::get('/me', [UserController::class, 'me'])->middleware('sso.verify');
```

### Access the token payload

Inside any controller or request handled by `sso.verify`:

```php
public function show(Request $request): JsonResponse
{
    $payload = $request->token_payload;
    // ['sub' => 1, 'email' => '...', 'role' => 'admin', ...]

    return response()->json(['user_id' => $payload['sub']]);
}
```

### Role-based access (example)

```php
Route::middleware(['sso.verify', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'index']);
});
```

Where `role` middleware reads from `$request->token_payload['role']`.

---

## Architecture

Tokens are **nested JWTs** — a signed JWS wrapped inside a JWE envelope:

```
JWE(RSA-OAEP-256 + A256GCM)
  └── JWS(RS256)
        └── JSON payload { sub, email, role, iss, iat, nbf, exp, jti }
```

This guarantees:
- **Confidentiality** — payload is encrypted; consuming services cannot read claims without the encryption private key.
- **Integrity** — inner signature ensures the auth service issued the token.
- **Non-repudiation** — only the auth service holds `sign_private.pem`.
