<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — DRSB</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #000;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            overflow: hidden;
        }

        /* ── Animated background ── */
        .bg {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
        }
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: .35;
            animation: drift 14s ease-in-out infinite alternate;
        }
        .orb-1 {
            width: 520px; height: 520px;
            background: radial-gradient(circle, #2F4BC0, transparent 70%);
            top: -120px; right: -120px;
            animation-duration: 18s;
        }
        .orb-2 {
            width: 480px; height: 480px;
            background: radial-gradient(circle, #E945F5, transparent 70%);
            bottom: -100px; left: -100px;
            animation-duration: 22s;
            animation-delay: -8s;
        }
        .orb-3 {
            width: 280px; height: 280px;
            background: radial-gradient(circle, #E945F5aa, transparent 70%);
            top: 30%; left: 60%;
            animation-duration: 13s;
            animation-delay: -4s;
            opacity: .18;
        }
        @keyframes drift {
            from { transform: translate(0, 0) scale(1); }
            to   { transform: translate(40px, 30px) scale(1.08); }
        }

        .grid-lines {
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.025) 1px, transparent 1px);
            background-size: 64px 64px;
        }

        /* ── Card ── */
        .wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 420px;
            padding: 1rem;
        }

        .card {
            background: rgba(0,0,0,.45);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 20px;
            padding: 2.25rem 2rem;
            box-shadow: 0 32px 64px rgba(0,0,0,.6);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
        }

        /* ── Logo ── */
        .logo-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 2rem;
        }
        .logo-icon {
            width: 52px; height: 52px;
            border-radius: 14px;
            background: linear-gradient(135deg, #E945F5, #2F4BC0);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: .75rem;
            box-shadow: 0 8px 24px rgba(233,69,245,.35);
        }
        .logo-icon svg { width: 26px; height: 26px; color: #fff; }
        .logo-title {
            font-size: 1.45rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: -.02em;
        }
        .logo-sub {
            margin-top: .25rem;
            font-size: .8rem;
            color: rgba(255,255,255,.45);
        }

        /* ── Errors ── */
        .alert-error {
            background: rgba(239,68,68,.1);
            border: 1px solid rgba(239,68,68,.2);
            color: #f87171;
            border-radius: 10px;
            padding: .7rem 1rem;
            font-size: .82rem;
            margin-bottom: 1.25rem;
        }

        /* ── Form fields ── */
        .field { margin-bottom: 1rem; }

        label {
            display: block;
            font-size: .78rem;
            font-weight: 500;
            color: rgba(255,255,255,.6);
            margin-bottom: .4rem;
            letter-spacing: .02em;
            text-transform: uppercase;
        }

        .input-wrap { position: relative; }

        input[type="email"],
        input[type="password"],
        input[type="text"] {
            width: 100%;
            padding: .65rem .9rem;
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 10px;
            color: #fff;
            font-size: .95rem;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
            -webkit-appearance: none;
        }
        input::placeholder { color: rgba(255,255,255,.25); }
        input:focus {
            border-color: rgba(233,69,245,.5);
            box-shadow: 0 0 0 3px rgba(233,69,245,.12);
        }

        /* password toggle */
        .pw-toggle {
            position: absolute;
            right: .75rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: rgba(255,255,255,.35);
            display: flex;
            align-items: center;
            padding: 0;
            transition: color .15s;
        }
        .pw-toggle:hover { color: rgba(255,255,255,.65); }

        /* ── Submit button ── */
        .btn-submit {
            width: 100%;
            margin-top: .5rem;
            padding: .75rem;
            background: linear-gradient(90deg, #E945F5, #2F4BC0);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: .95rem;
            font-weight: 600;
            cursor: pointer;
            letter-spacing: .01em;
            transition: opacity .2s;
        }
        .btn-submit:hover { opacity: .88; }
        .btn-submit:active { opacity: .75; }

        /* ── Footer link ── */
        .footer-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: .82rem;
            color: rgba(255,255,255,.35);
        }
        .footer-link a {
            color: #E945F5;
            text-decoration: none;
            font-weight: 500;
        }
        .footer-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="bg">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
    <div class="grid-lines"></div>
</div>

<div class="wrapper">
    <div class="card">

        {{-- Logo --}}
        <div class="logo-wrap">
            <div class="logo-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16.5 9.4 7.55 4.24"/><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.29 7 12 12 20.71 7"/><line x1="12" y1="22" x2="12" y2="12"/>
                </svg>
            </div>
            <div class="logo-title">DRSB Logistics</div>
            <div class="logo-sub">Create your account</div>
        </div>

        {{-- Server errors --}}
        @if ($errors->any())
            <div class="alert-error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('sso.register') }}">
            @csrf
            @if (!empty($redirectTo))
                <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
            @endif

            <div class="field">
                <label for="name">Full Name</label>
                <input id="name" type="text" name="name"
                       value="{{ old('name') }}"
                       placeholder="Your name"
                       autocomplete="name"
                       required autofocus>
            </div>

            <div class="field">
                <label for="email">Email</label>
                <input id="email" type="email" name="email"
                       value="{{ old('email') }}"
                       placeholder="you@drsb.com"
                       autocomplete="email"
                       required>
            </div>

            <div class="field">
                <label for="password">Password</label>
                <div class="input-wrap">
                    <input id="password" type="password" name="password"
                           placeholder="••••••••"
                           autocomplete="new-password"
                           required>
                    <button type="button" class="pw-toggle" onclick="togglePw('password','eye-1')" aria-label="Toggle password">
                        <svg id="eye-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="field">
                <label for="password_confirmation">Confirm Password</label>
                <div class="input-wrap">
                    <input id="password_confirmation" type="password" name="password_confirmation"
                           placeholder="••••••••"
                           autocomplete="new-password"
                           required>
                    <button type="button" class="pw-toggle" onclick="togglePw('password_confirmation','eye-2')" aria-label="Toggle confirm password">
                        <svg id="eye-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-submit">Create Account</button>
        </form>

        <div class="footer-link">
            Already have an account? <a href="{{ route('sso.login.form') }}">Sign In</a>
        </div>

    </div>
</div>

<script>
function togglePw(inputId, iconId) {
    var inp = document.getElementById(inputId);
    var icon = document.getElementById(iconId);
    if (inp.type === 'password') {
        inp.type = 'text';
        icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>';
    } else {
        inp.type = 'password';
        icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
    }
}
</script>
</body>
</html>
