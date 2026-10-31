<?php

namespace App\Services;

use App\Models\ActivityLog;

class ActivityLogger
{
    public static function log(string $action, string $description, ?int $companyId = null): void
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'company_id' => $companyId ?? (auth()->check() ? auth()->user()->current_company_id : null),
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
