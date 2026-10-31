<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureOtpVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            if (!$user->is_otp_verified && !$request->routeIs('otp.verify') && !$request->routeIs('logout')) {
                return redirect()->route('otp.verify');
            }
        }

        return $next($request);
    }
}
