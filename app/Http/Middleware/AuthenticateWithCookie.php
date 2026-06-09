<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthenticateWithCookie
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
        $authHeader = $request->header('Authorization');
        \Illuminate\Support\Facades\Log::info('AuthenticateWithCookie: has auth_token cookie: ' . ($request->hasCookie('auth_token') ? 'YES' : 'NO') . ', authHeader: ' . ($authHeader ?? 'NULL'));
        if ($request->hasCookie('auth_token') && (!$authHeader || str_contains($authHeader, 'cookie_session'))) {
            $token = $request->cookie('auth_token');
            \Illuminate\Support\Facades\Log::info('AuthenticateWithCookie: injecting decrypted cookie token: ' . substr($token, 0, 10) . '...');
            $request->headers->set('Authorization', 'Bearer ' . $token);
        }

        return $next($request);
    }
}
