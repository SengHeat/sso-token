# sengheat/sso-token

A Laravel SSO package — one central auth-service issues tokens, every other service verifies them without HTTP calls.

---

## How It Works

```
┌─────────────────────────────────────────────────────────┐
│                    auth-service (Issuer)                 │
│  /sso/login  →  user logs in  →  issues api_token       │
└───────────────────────────┬─────────────────────────────┘
                            │ token
          ┌─────────────────┼─────────────────┐
          ▼                 ▼                 ▼
   order-service      logistic-portal    mobile-app
   verifies token     verifies token     verifies token
   via auth_db        via auth_db        via auth_db
   (no HTTP call)     (no HTTP call)     (no HTTP call)
```

---

## Installation

```bash
composer require sengheat/sso-token
php artisan sso:install
```

---

## Part 1 — Auth Service (Issuer)

The service that owns the `users` table and issues tokens.

### 1. Enable issuer mode in `.env`

```env
SSO_FORM_AUTH=true
SSO_FORM_AUTH_REGISTER=true
SSO_ALLOWED_REDIRECTS=http://localhost:3000/sso/callback,https://portal.yourdomain.com/sso/callback
```

### 2. Publish and configure `config/sso.php`

```php
return [
    'form_auth' => [
        'enabled'        => env('SSO_FORM_AUTH', false),
        'allow_register' => env('SSO_FORM_AUTH_REGISTER', true),
    ],

    'allowed_redirects' => array_filter(explode(',', env('SSO_ALLOWED_REDIRECTS', ''))),

    'redirect_after_login'  => env('SSO_REDIRECT_AFTER_LOGIN', '/dashboard'),
    'redirect_after_logout' => env('SSO_REDIRECT_AFTER_LOGOUT', '/sso/login'),

    'user_model'      => env('SSO_USER_MODEL', \App\Models\User::class),
    'register_routes' => true,
    'run_migrations'  => true,

    'cache_store'     => env('SSO_CACHE_STORE', null),
    'token_cache_ttl' => env('SSO_TOKEN_CACHE_TTL', 300),
];
```

### 3. Add `HasSSOProfile` trait to User model

```php
use SengHeat\LaravelSso\Traits\HasSSOProfile;

class User extends Authenticatable
{
    use HasSSOProfile;

    protected $fillable = [
        'name', 'email', 'password', 'api_token',
        'sso_provider', 'sso_provider_id', 'sso_token', 'sso_avatar',
    ];

    protected $hidden = [
        'password', 'remember_token', 'api_token',
        'sso_token', 'sso_provider', 'sso_provider_id', 'sso_avatar',
    ];
}
```

### 4. Add `api` guard to `config/auth.php`

```php
'guards' => [
    'web' => ['driver' => 'session', 'provider' => 'users'],
    'api' => ['driver' => 'token',   'provider' => 'users', 'hash' => true],
],
```

### 5. Run migration

```bash
php artisan migrate
```

Adds to `users` table: `sso_provider`, `sso_provider_id`, `sso_token`, `sso_avatar`, `api_token` (indexed).

### Available routes (auto-registered)

| Method | URI | Description |
|--------|-----|-------------|
| GET | `/sso/login` | Login page (form) |
| POST | `/sso/login` | Process login |
| GET | `/sso/register` | Register page |
| POST | `/sso/register` | Process register |
| POST | `/api/sso/login` | API login → `{user, token}` |
| POST | `/api/sso/register` | API register → `{user, token}` |
| POST | `/api/sso/exchange` | Exchange one-time code → `{user, token}` |
| POST | `/api/sso/logout` | Revoke token |
| GET | `/api/sso/user` | Current user info |

---

## Part 2 — Other Services (Consumers)

Order service, product service, any service that needs to verify tokens.

### 1. Install package

```bash
composer require sengheat/sso-token
```

### 2. Add `auth_db` connection to `config/database.php`

```php
'auth_db' => [
    'driver'   => 'pgsql',
    'host'     => env('AUTH_DB_HOST', '127.0.0.1'),
    'port'     => env('AUTH_DB_PORT', '5432'),
    'database' => env('AUTH_DB_DATABASE', 'auth_service'),
    'username' => env('AUTH_DB_USERNAME', 'postgres'),
    'password' => env('AUTH_DB_PASSWORD', ''),
],
```

### 3. Add `sso` guard to `config/auth.php`

```php
'guards' => [
    'sso' => ['driver' => 'token', 'provider' => 'sso_users', 'hash' => true],
],

'providers' => [
    'sso_users' => ['driver' => 'eloquent', 'model' => App\Models\User::class],
],
```

### 4. User model — points to `auth_db`

```php
use SengHeat\LaravelSso\Traits\HasSSOProfile;

class User extends Authenticatable
{
    use HasSSOProfile;

    protected $connection = 'auth_db';

    protected $fillable = [
        'name', 'email', 'api_token',
        'sso_provider', 'sso_provider_id', 'sso_avatar',
    ];
}
```

### 5. Register middleware in `bootstrap/app.php`

```php
use SengHeat\LaravelSso\Http\Middleware\EnsureSSOUser;
use SengHeat\LaravelSso\Http\Middleware\CacheSsoToken;

->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'sso.only'  => EnsureSSOUser::class,
        'sso.cache' => CacheSsoToken::class,
    ]);
})
```

### 6. Protect routes

```php
// sso.cache runs first — loads user from Redis or auth_db
// auth:sso runs second — user already set, no DB query
Route::middleware(['sso.cache:sso', 'auth:sso'])->group(function () {
    Route::apiResource('orders', OrderController::class);
});
```

### 7. `.env`

```env
AUTH_DB_HOST=127.0.0.1
AUTH_DB_PORT=5432
AUTH_DB_DATABASE=auth_service
AUTH_DB_USERNAME=order_svc
AUTH_DB_PASSWORD=your_password

SSO_CACHE_STORE=redis
SSO_TOKEN_CACHE_TTL=300
SSO_FORM_AUTH=false
```

### How token verification works

```
Request: Authorization: Bearer <token>
    │
    ├─ sso.cache:sso
    │      HIT  → load from Redis → Auth::guard('sso')->setUser($user)
    │      MISS → query auth_db  → store in Redis for 300s
    │
    └─ auth:sso → user already set → skip DB ✓
```

### PostgreSQL security (production)

```sql
-- Create restricted read-only user per service
CREATE USER order_svc WITH PASSWORD 'secret';
GRANT CONNECT ON DATABASE auth_service TO order_svc;
GRANT USAGE ON SCHEMA public TO order_svc;
GRANT SELECT ON TABLE public.users TO order_svc;
```

```conf
# pg_hba.conf — only allow order-service IP
host  auth_service  order_svc  <ORDER_SERVICE_IP>/32  scram-sha-256
host  auth_service  all        0.0.0.0/0              reject
```

---

## Part 3 — Web Portal (Next.js / React)

Token is **never** visible in the URL. A 30-second one-time code is used instead.

### Flow

```
1. Portal → redirect to auth-service login with redirect_to
2. User logs in on auth-service
3. Auth-service → redirect back with ?code=xxx  (30s expiry, one-time)
4. Portal → POST /api/sso/exchange with code → receives real token
5. Token stored in app state / secure storage
```

### Step 1 — Redirect to auth-service

```ts
// .env.local
// NEXT_PUBLIC_SSO_URL=http://localhost:8000
// NEXT_PUBLIC_SSO_REDIRECT_URI=http://localhost:3000/sso/callback

const params = new URLSearchParams({ redirect_to: process.env.NEXT_PUBLIC_SSO_REDIRECT_URI! })
window.location.href = `${process.env.NEXT_PUBLIC_SSO_URL}/sso/login?${params}`
```

### Step 2 — Callback page (`/sso/callback`)

```tsx
"use client"
import { useEffect } from "react"
import { useRouter, useSearchParams } from "next/navigation"

export default function SsoCallbackPage() {
    const router = useRouter()
    const searchParams = useSearchParams()

    useEffect(() => {
        const code = searchParams.get("code")
        if (!code) { router.replace("/login"); return }

        fetch(`${process.env.NEXT_PUBLIC_SSO_URL}/api/sso/exchange`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ code }),
        })
        .then(res => res.json())
        .then(({ user, token }) => {
            // store token in zustand / context / cookie
            storeLogin(user, token)
            router.replace("/dashboard")
        })
        .catch(() => router.replace("/login"))
    }, [])

    return <div>Signing you in…</div>
}
```

### Step 3 — Use token on any service

```ts
fetch("http://localhost:8001/api/orders", {
    headers: { Authorization: `Bearer ${token}` }
})
```

### `.env.local`

```env
NEXT_PUBLIC_SSO_URL=http://localhost:8000
NEXT_PUBLIC_SSO_REDIRECT_URI=http://localhost:3000/sso/callback
```

---

## Part 4 — Mobile App (React Native / Flutter)

Mobile calls the **API endpoints directly** — no browser redirect needed.

### Login

```
POST /api/sso/login
{ "email": "user@example.com", "password": "password" }

→ { "user": {...}, "token": "xxxx" }
```

Store token securely (iOS Keychain / Android Keystore).

### Use token on any service

```
GET  http://order-service/api/orders
Authorization: Bearer xxxx
```

### Logout

```
POST /api/sso/logout
Authorization: Bearer xxxx
→ token revoked on all services instantly
```

### React Native

```ts
import * as SecureStore from "expo-secure-store"

// Login
const { user, token } = await fetch("http://auth:8000/api/sso/login", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ email, password }),
}).then(r => r.json())

await SecureStore.setItemAsync("sso_token", token)

// Use on any service
const token = await SecureStore.getItemAsync("sso_token")
const orders = await fetch("http://orders:8001/api/orders", {
    headers: { Authorization: `Bearer ${token}` },
}).then(r => r.json())
```

### Flutter

```dart
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

final storage = FlutterSecureStorage();

// Login
final res = await http.post(Uri.parse('http://auth:8000/api/sso/login'),
    headers: {'Content-Type': 'application/json'},
    body: jsonEncode({'email': email, 'password': password}));
final token = jsonDecode(res.body)['token'];
await storage.write(key: 'sso_token', value: token);

// Use on any service
final token = await storage.read(key: 'sso_token');
final orders = await http.get(Uri.parse('http://orders:8001/api/orders'),
    headers: {'Authorization': 'Bearer $token'});
```

---

## Trait Helpers

```php
$user->isSSOUser();               // true if logged in via OAuth provider
$user->usesProvider('google');    // true | false
$user->ssoAvatar('/default.png'); // avatar URL with fallback

$user->generateApiToken();        // generates token, stores SHA256 hash, returns plain
$user->revokeApiToken();          // sets api_token = null (logout)

User::fromProvider('google')->get();  // query scope
User::nativeUsers()->get();           // users without OAuth
```

---

## Security Summary

| Concern | Solution |
|---------|----------|
| Token in URL | One-time code exchange — token never in URL |
| Token storage | SHA256 hash in DB — plain token only in HTTP response |
| Open redirect | `SSO_ALLOWED_REDIRECTS` whitelist |
| DB access | Dedicated read-only PostgreSQL user per service |
| Network | `pg_hba.conf` IP whitelist on auth_db port |
| Brute force | `throttle:20,1` on login routes |
| Cache | Redis with TTL — stale tokens auto-expire |

---

## Environment Variables Reference

| Variable | Service | Default | Description |
|----------|---------|---------|-------------|
| `SSO_FORM_AUTH` | Issuer | `false` | Enable built-in login/register |
| `SSO_FORM_AUTH_REGISTER` | Issuer | `true` | Allow new registrations |
| `SSO_ALLOWED_REDIRECTS` | Issuer | — | Comma-separated portal callback URLs |
| `SSO_REDIRECT_AFTER_LOGIN` | Issuer | `/dashboard` | Fallback redirect after login |
| `SSO_REDIRECT_AFTER_LOGOUT` | Issuer | `/sso/login` | Redirect after logout |
| `SSO_CACHE_STORE` | Consumer | `null` (default) | Cache driver: `redis`, `file` |
| `SSO_TOKEN_CACHE_TTL` | Consumer | `300` | Token cache TTL in seconds |
| `AUTH_DB_HOST` | Consumer | `127.0.0.1` | Auth-service DB host |
| `AUTH_DB_PORT` | Consumer | `5432` | Auth-service DB port |
| `AUTH_DB_DATABASE` | Consumer | `auth_service` | Auth-service DB name |
| `AUTH_DB_USERNAME` | Consumer | — | Read-only DB user |
| `AUTH_DB_PASSWORD` | Consumer | — | DB password |

---

## License

MIT — Seng Heat
