<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class Authenticate extends Middleware
{
    // protected function redirectTo(Request $request): ?string
    // {
    //     return $request->expectsJson() ? null : route('login');
    // }

    public function handle($request, Closure $next, ...$guards)
    {
        try {
            $token = JWTAuth::parseToken()->authenticate();
            if (!$token) {
                throw new AuthenticationException('Unauthenticated.');
            }
            return parent::handle($request, $next, ...$guards);
        } catch (AuthenticationException $exception) {
            throw $exception;
        }
    }
}
