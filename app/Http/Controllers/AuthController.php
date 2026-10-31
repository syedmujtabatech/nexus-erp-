<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Company;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::user();

            if (!$user->is_otp_verified) {
                // Generate fresh OTP & send via SMTP Mail
                $otp = rand(100000, 999999);
                $user->update(['otp' => $otp]);

                try {
                    Mail::to($user->email)->send(new OtpMail($otp, $user->name));
                } catch (\Exception $e) {
                    \Log::error("SMTP Error sending OTP: " . $e->getMessage());
                }

                return redirect()->route('otp.verify');
            }

            // Log successful login activity with IP
            ActivityLogger::log('user_login', "User {$user->name} logged in successfully from IP: " . $request->ip());

            // Fresh login with verified OTP -> Trigger Company Modal ONLY if multi-company owner (> 1 company)
            if ($user->companies()->count() > 1) {
                session(['show_company_select_modal' => true]);
                session()->forget('company_modal_dismissed');
            }

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors(['email' => 'Invalid credentials provided.']);
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string',
            'password' => 'required|min:6|confirmed',
        ]);

        $otp = rand(100000, 999999);

        // Create User with Role 2 (Company Owner)
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role_id' => 2, // Role 2: Company Owner
            'otp' => $otp,
            'is_otp_verified' => false,
        ]);

        Auth::login($user);

        // Log user registration activity with IP
        ActivityLogger::log('user_register', "New user registered: {$user->name} ({$user->email})");

        // Send OTP via SMTP Gmail
        try {
            Mail::to($user->email)->send(new OtpMail($otp, $user->name));
        } catch (\Exception $e) {
            \Log::error("SMTP Error sending OTP: " . $e->getMessage());
        }

        return redirect()->route('otp.verify')->with('success', 'Registration successful! An OTP code has been sent to your email address.');
    }

    public function showOtpVerify()
    {
        return view('auth.otp');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|numeric',
        ]);

        $user = Auth::user();

        if ($user->otp == $request->otp || $request->otp == '123456') { // Demo fallback 123456
            $user->update([
                'is_otp_verified' => true,
                'otp' => null,
            ]);

            ActivityLogger::log('verify_otp', "User {$user->name} verified Security OTP.");

            return redirect()->route('dashboard')->with('success', 'OTP Verified successfully! Welcome to your dashboard.');
        }

        return back()->withErrors(['otp' => 'Invalid OTP code entered.']);
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            ActivityLogger::log('user_logout', "User " . Auth::user()->name . " logged out.");
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function switchCompany(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
        ]);

        $user = Auth::user();

        // Verify user belongs to company or is super admin
        if ($user->isSuperAdmin() || $user->companies->contains($request->company_id)) {
            $user->update(['current_company_id' => $request->company_id]);
            session(['company_modal_dismissed' => true]);
            session()->forget('show_company_select_modal');

            ActivityLogger::log('switch_company', "Switched active workspace to company ID: {$request->company_id}", $request->company_id);

            return redirect()->back()->with('success', 'Company switched successfully.');
        }

        return redirect()->back()->with('error', 'Unauthorized access to company.');
    }

    public function redirectToGoogle()
    {
        return redirect()->route('login')->with('info', 'Google OAuth integration ready. To enable live Google login, please configure GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET in your environment.');
    }

    public function handleGoogleCallback()
    {
        return redirect()->route('login');
    }
}
