<div class="sso-buttons">
    @foreach (config('sso.providers') as $provider => $cfg)
        @if (!empty($cfg['client_id']))
            <a href="{{ route('sso.redirect', $provider) }}"
               class="sso-btn sso-btn--{{ $provider }}">
                Sign in with {{ ucfirst($provider) }}
            </a>
        @endif
    @endforeach
</div>
