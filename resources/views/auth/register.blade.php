<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>
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
        .link { text-align: center; margin-top: 1.25rem; font-size: .85rem; color: #6b7280; }
        .link a { color: #6366f1; text-decoration: none; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Create Account</h2>

        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('sso.register') }}">
            @csrf
            <label for="name">Name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus>

            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required>

            <label for="password">Password</label>
            <input id="password" type="password" name="password" required>

            <label for="password_confirmation">Confirm Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required>

            <button type="submit" class="btn">Create Account</button>
        </form>

        <div class="link">
            Already have an account? <a href="{{ route('sso.login.form') }}">Sign In</a>
        </div>
    </div>
</body>
</html>
