<?php

return [
    'mode'         => env('SSO_MODE', 'verify'),
    'issuer'       => env('SSO_AUTH_ISSUER', 'http://localhost:8000'),
    'ttl'          => env('SSO_TOKEN_TTL', 15),
    'refresh_ttl'  => env('SSO_REFRESH_TTL', 7),
    'sign_public'  => env('SSO_SIGN_PUBLIC_KEY',  storage_path('keys/sign_public.pem')),
    'sign_private' => env('SSO_SIGN_PRIVATE_KEY', storage_path('keys/sign_private.pem')),
    'enc_public'   => env('SSO_ENC_PUBLIC_KEY',   storage_path('keys/enc_public.pem')),
    'enc_private'  => env('SSO_ENC_PRIVATE_KEY',  storage_path('keys/enc_private.pem')),
];