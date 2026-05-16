<?php

namespace SengHeat\LaravelSso\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use SengHeat\LaravelSso\Events\SSOLoginFailed;
use SengHeat\LaravelSso\Events\SSOLoginSucceeded;
use SengHeat\LaravelSso\Events\SSOUserCreated;
use SengHeat\LaravelSso\Facades\SSO;

class SSOController extends Controller
{
    /**
     * Step 1 — Redirect the user to the IdP.
     */
    public function redirect(string $provider): RedirectResponse
    {
        $this->ensureProviderIsEnabled($provider);

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Step 2 — Handle the IdP callback and log the user in.
     */
    public function callback(string $provider): RedirectResponse
    {
        $this->ensureProviderIsEnabled($provider);

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Throwable $e) {
            event(new SSOLoginFailed($provider, $e->getMessage()));

            return redirect(SSO::redirectAfterLogout())
                ->withErrors(['sso' => 'SSO authentication failed. Please try again.']);
        }

        $userModel = SSO::userModel();
        $isNewUser = false;
        $user      = $userModel::where('email', $socialUser->getEmail())->first();

        if (! $user) {
            $isNewUser = true;
            $user = $userModel::create([
                'name'              => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
                'email'             => $socialUser->getEmail(),
                'sso_provider'      => $provider,
                'sso_provider_id'   => $socialUser->getId(),
                'sso_token'         => $socialUser->token,
                'sso_avatar'        => $socialUser->getAvatar(),
                'email_verified_at' => now(),
                'password'          => null,
            ]);

            event(new SSOUserCreated($user, $provider));
        } else {
            $user->update([
                'sso_provider'    => $provider,
                'sso_provider_id' => $socialUser->getId(),
                'sso_token'       => $socialUser->token,
                'sso_avatar'      => $socialUser->getAvatar(),
            ]);
        }

        Auth::login($user, remember: true);

        event(new SSOLoginSucceeded($user, $provider, $isNewUser));

        return redirect()->intended(SSO::redirectAfterLogin());
    }

    /**
     * Step 3 — Log the user out (clear local session only).
     */
    public function logout(): RedirectResponse
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect(SSO::redirectAfterLogout());
    }

    protected function ensureProviderIsEnabled(string $provider): void
    {
        if (! SSO::isEnabled($provider)) {
            abort(404, "SSO provider [{$provider}] is not enabled.");
        }
    }
}
