<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\User;
use App\Models\Role;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function companies()
    {
        $companies = Company::with(['owner', 'users'])->latest()->paginate(20);
        return view('admin.companies', compact('companies'));
    }

    public function users()
    {
        $users = User::with(['role', 'companies'])->latest()->paginate(20);
        $roles = Role::all();
        return view('admin.users', compact('users', 'roles'));
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'is_otp_verified' => true,
        ]);

        ActivityLogger::log('create_user_admin', "Super Admin created new user: {$user->name} (Role ID: {$user->role_id})");

        return redirect()->back()->with('success', "System User '{$user->name}' created successfully!");
    }

    public function updateUser(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = User::findOrFail($id);
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role_id' => $request->role_id,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        ActivityLogger::log('update_user_admin', "Super Admin updated user: {$user->name}");

        return redirect()->back()->with('success', "System User '{$user->name}' updated successfully.");
    }

    public function destroyUser($id)
    {
        $user = User::findOrFail($id);
        if ($user->id == auth()->id()) {
            return redirect()->back()->with('error', 'You cannot delete yourself!');
        }
        $userName = $user->name;
        $user->delete();

        ActivityLogger::log('delete_user_admin', "Super Admin deleted user: {$userName}");

        return redirect()->back()->with('success', "User '{$userName}' deleted successfully.");
    }
}
