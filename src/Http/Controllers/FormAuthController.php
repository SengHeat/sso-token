<?php

namespace SengHeat\LaravelSso\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class FormAuthController extends Controller
{
    public function showLogin(Request $request)
    {
        return view('sso::auth.login', [
            'redirectTo' => $this->validateRedirectTo($request->query('redirect_to')),
        ]);
    }

    public function showRegister(Request $request)
    {
        return view('sso::auth.register', [
            'redirectTo' => $this->validateRedirectTo($request->query('redirect_to')),
        ]);
    }

    public function login(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'email'       => ['required', 'email'],
            'password'    => ['required', 'string'],
            'redirect_to' => ['nullable', 'string'],
        ]);

        $userModel = config('sso.user_model', \App\Models\User::class);
        $user      = $userModel::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            if ($request->expectsJson()) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            return back()->withErrors(['email' => 'The provided credentials are incorrect.'])->withInput();
        }

        $token = $user->generateApiToken();

        if ($request->expectsJson()) {
            return response()->json(['user' => $user, 'token' => $token]);
        }

        $redirectTo = $this->validateRedirectTo($data['redirect_to'] ?? null);

        if ($redirectTo) {
            return redirect($redirectTo . '?token=' . $token);
        }

        return redirect()->intended(config('sso.redirect_after_login', '/dashboard'));
    }

    public function register(Request $request): JsonResponse|RedirectResponse
    {
        if (! config('sso.form_auth.allow_register', true)) {
            abort(403, 'Registration is disabled.');
        }

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'email', 'unique:users,email'],
            'password'    => ['required', 'string', 'min:8', 'confirmed'],
            'redirect_to' => ['nullable', 'string'],
        ]);

        $userModel = config('sso.user_model', \App\Models\User::class);

        $user  = $userModel::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $token = $user->generateApiToken();

        if ($request->expectsJson()) {
            return response()->json(['user' => $user, 'token' => $token], 201);
        }

        $redirectTo = $this->validateRedirectTo($data['redirect_to'] ?? null);

        if ($redirectTo) {
            return redirect($redirectTo . '?token=' . $token);
        }

        return redirect(config('sso.redirect_after_login', '/dashboard'));
    }

    public function logout(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        if ($user) {
            $user->revokeApiToken();
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Logged out successfully.']);
        }

        return redirect(config('sso.redirect_after_logout', '/sso/login'));
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    protected function validateRedirectTo(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $allowed = config('sso.allowed_redirects', []);

        if (empty($allowed)) {
            return null;
        }

        foreach ($allowed as $allowedUrl) {
            if (str_starts_with($url, rtrim($allowedUrl, '/'))) {
                return $url;
            }
        }

        return null;
    }
}
