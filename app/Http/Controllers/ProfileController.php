<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\File;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $company = $user->currentCompany;
        return view('profile.show', compact('user', 'company'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:50',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:4096',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ];

        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $filename = 'avatar_' . $user->id . '_' . time() . '.' . $avatar->getClientOriginalExtension();
            $path = public_path('uploads/avatars');

            if (!File::isDirectory($path)) {
                File::makeDirectory($path, 0777, true, true);
            }

            // Remove old avatar if exists
            if ($user->avatar && File::exists(public_path($user->avatar))) {
                File::delete(public_path($user->avatar));
            }

            $avatar->move($path, $filename);
            $data['avatar'] = 'uploads/avatars/' . $filename;
        }

        $user->update($data);

        ActivityLogger::log('update_profile', "User {$user->name} updated their profile settings");

        return redirect()->back()->with('success', 'Profile information updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()->withErrors(['current_password' => 'Current password does not match our records.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        ActivityLogger::log('change_password', "User {$user->name} changed their account password");

        return redirect()->back()->with('success', 'Account password updated successfully.');
    }
}
