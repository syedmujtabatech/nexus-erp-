<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivityLog;

class ActivityLogController extends Controller
{
    public function index()
    {
        $query = ActivityLog::with(['user', 'company'])->latest();

        // Scope to current company if not super admin
        if (!auth()->user()->isSuperAdmin()) {
            $query->where('company_id', auth()->user()->current_company_id);
        }

        $logs = $query->paginate(50);
        return view('activity.index', compact('logs'));
    }
}
