<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class TeamController extends Controller
{
    public function index()
    {
        $company = Auth::user()->currentCompany;
        $users = $company ? $company->users : collect();
        return view('team.index', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        $company = Auth::user()->currentCompany;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role_id' => 3, // Staff / Subuser
            'is_otp_verified' => true,
            'current_company_id' => $company->id,
        ]);

        $permissions = $request->permissions ?? [];
        $company->users()->attach($user->id, ['role_id' => 3, 'permissions' => json_encode($permissions)]);

        \App\Services\ActivityLogger::log('team_invite', "Invited team sub-user {$user->name} ({$user->email})", $company->id);

        return redirect()->back()->with('success', 'Team member invited successfully with assigned permissions.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
        ]);

        $company = Auth::user()->currentCompany;
        $user = User::findOrFail($id);

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);

        $permissions = $request->permissions ?? [];
        $company->users()->updateExistingPivot($user->id, [
            'permissions' => json_encode($permissions),
        ]);

        \App\Services\ActivityLogger::log('team_update', "Updated permissions/details for team sub-user {$user->name}", $company->id);

        return redirect()->back()->with('success', "Team member '{$user->name}' updated successfully.");
    }

    public function destroy($id)
    {
        $company = Auth::user()->currentCompany;
        $user = User::findOrFail($id);

        if ($user->id == Auth::id()) {
            return redirect()->back()->with('error', 'You cannot remove yourself from the company team.');
        }

        $userName = $user->name;
        $company->users()->detach($user->id);

        \App\Services\ActivityLogger::log('team_remove', "Removed user {$userName} from company team", $company->id);

        return redirect()->back()->with('success', "User '{$userName}' removed from team.");
    }
}
