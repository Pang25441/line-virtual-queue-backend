<?php

namespace App\Http\Middleware;

use App\Http\Services\LineService;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LineLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $accessToken = $request->bearerToken();

        if (!$accessToken) {
            throw new AuthenticationException('Line Account Unauthenticated.');
        }

        try {
            $lineService = new LineService(['accessToken' => $accessToken]);

            $lineConfig = $lineService->getLineConfig();

            if (!$lineConfig) {
                return response(
                    [
                        'status' => 400,
                        'data' => ['error' => "LINE_CONFIG_EMPTY"],
                        'message' => 'Line Config Not Found'
                    ],
                    200
                );
            }

            $request->lineService = $lineService;
        } catch (\Throwable $th) {
            Log::error('LineLogin Middleware: ' . $th->getMessage());
            throw new AuthenticationException('Line Account Unauthenticated.');
        }

        return $next($request);
    }
}
