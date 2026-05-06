<?php

namespace SengHeat\SsoToken\Middleware;

use Closure;
use Illuminate\Http\Request;
use RuntimeException;
use Throwable;
use SengHeat\SsoToken\Services\TokenService;

class VerifyToken
{
    public function __construct(private TokenService $tokenService) {}

    public function handle(Request $request, Closure $next): mixed
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'No token provided'], 401);
        }

        try {
            $payload = $this->tokenService->verify($token);
            $request->merge(['token_payload' => $payload]);

            return $next($request);
        } catch (RuntimeException $e) {
            $response = ['error' => $e->getMessage()];

            if (config('app.env') !== 'production') {
                $response['debug'] = $e->getMessage();
            }

            return response()->json($response, 401);
        } catch (Throwable $e) {
            $response = ['error' => 'Token invalid'];

            if (config('app.env') !== 'production') {
                $response['debug'] = $e->getMessage();
            }

            return response()->json($response, 401);
        }
    }
}
