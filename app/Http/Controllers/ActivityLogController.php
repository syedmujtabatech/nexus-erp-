<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivityLog;

class ActivityLogController extends Controller
{
    public function index()
    {
        $query = ActivityLog::with(['user', 'company'])->latest();

        // Scope: Super Admin sees all; regular users see their own user actions and active company logs
        if (!auth()->user()->isSuperAdmin()) {
            $companyId = auth()->user()->current_company_id;
            $userId = auth()->id();
            $query->where(function($q) use ($companyId, $userId) {
                $q->where('user_id', $userId);
                if ($companyId) {
                    $q->orWhere('company_id', $companyId);
                }
            });
        }

        $logs = $query->paginate(50);
        return view('activity.index', compact('logs'));
    }
}
