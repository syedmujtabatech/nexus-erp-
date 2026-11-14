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

            // Super Admin NEVER requires OTP verification
            if ($user->isSuperAdmin()) {
                return $next($request);
            }

            // Allow unverified users to reach the dashboard where the OTP popup modal is displayed
            if (!$user->is_otp_verified && !$request->routeIs('dashboard') && !$request->routeIs('otp.verify') && !$request->routeIs('logout')) {
                return redirect()->route('dashboard');
            }
        }

        return $next($request);
    }
}
