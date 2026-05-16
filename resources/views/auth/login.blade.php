<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: sans-serif; background: #f3f4f6; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .card { background: #fff; border-radius: 10px; box-shadow: 0 2px 12px rgba(0,0,0,.1); padding: 2rem; width: 100%; max-width: 420px; }
        h2 { margin-bottom: 1.5rem; font-size: 1.4rem; color: #111; text-align: center; }
        label { display: block; font-size: .85rem; color: #374151; margin-bottom: .3rem; }
        input { width: 100%; padding: .6rem .8rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: .95rem; margin-bottom: 1rem; }
        input:focus { outline: none; border-color: #6366f1; }
        .btn { width: 100%; padding: .7rem; background: #6366f1; color: #fff; border: none; border-radius: 6px; font-size: 1rem; cursor: pointer; }
        .btn:hover { background: #4f46e5; }
        .error { color: #dc2626; font-size: .85rem; margin-bottom: 1rem; }
        .divider { display: flex; align-items: center; gap: .75rem; margin: 1.25rem 0; color: #9ca3af; font-size: .85rem; }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: #e5e7eb; }
        .sso-btn { display: block; width: 100%; padding: .65rem; border: 1px solid #d1d5db; border-radius: 6px; text-align: center; text-decoration: none; color: #374151; font-size: .9rem; margin-bottom: .5rem; }
        .sso-btn:hover { background: #f9fafb; }
        .link { text-align: center; margin-top: 1.25rem; font-size: .85rem; color: #6b7280; }
        .link a { color: #6366f1; text-decoration: none; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Sign In</h2>

        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        @if (config('sso.form_auth.enabled', false))
        <form method="POST" action="{{ route('sso.login') }}">
            @csrf
            @if (!empty($redirectTo))
                <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
            @endif
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>

            <label for="password">Password</label>
            <input id="password" type="password" name="password" required>

            <button type="submit" class="btn">Sign In</button>
        </form>
        @endif

        @php $providers = array_filter(config('sso.providers', []), fn($p) => !empty($p['client_id'])); @endphp

        @if (config('sso.form_auth.enabled', false) && count($providers))
            <div class="divider">or continue with</div>
        @endif

        @foreach ($providers as $provider => $cfg)
            <a href="{{ route('sso.redirect', $provider) }}" class="sso-btn">
                Sign in with {{ ucfirst($provider) }}
            </a>
        @endforeach

        @if (config('sso.form_auth.allow_register', true) && config('sso.form_auth.enabled', false))
            <div class="link">
                Don't have an account? <a href="{{ route('sso.register.form') }}">Register</a>
            </div>
        @endif
    </div>
</body>
</html>
