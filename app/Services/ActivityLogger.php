<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public static function log(
        $action,
        $entityType,
        $entityId = null,
        $companyId = null,
        $oldData = null,
        $newData = null
    ) {

        ActivityLog::create([
            'user_id' => Auth::id(),
            'company_id' => $companyId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'old_data' => $oldData,
            'new_data' => $newData,
            'created_at' => now(),
        ]);

    }
}
