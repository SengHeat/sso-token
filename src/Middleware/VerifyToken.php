<?php

namespace SengHeat\SsoToken\Middleware;

use Closure;
use Illuminate\Http\Request;
use SengHeat\SsoToken\Services\SsoManager;
use SengHeat\SsoToken\Services\TokenService;
use Throwable;

class VerifyToken
{
    public function __construct(
        private TokenService $tokenService,
        private SsoManager $ssoManager,
    ) {}

    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'No token provided'], 401);
        }

        try {
            $payload = $this->tokenService->verify($token);

            // Keep backward-compat request attribute
            $request->merge(['token_payload' => $payload]);

            // Populate the Passport-style helper
            $this->ssoManager->setPayload($payload, $token);

            return $next($request);

        } catch (Throwable $e) {
            $response = ['error' => $e->getMessage()];

            // Show debug info outside production
            if (!app()->isProduction()) {
                $response['debug'] = [
                    'message' => $e->getMessage(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                ];
            }

            return response()->json($response, 401);
        }
    }
}