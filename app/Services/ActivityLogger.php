<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ActivityLogger
{
    /**
     * Create an activity log record.
     *
     * @param  string  $action
     * @param  string  $entityType
     * @param  int|string|null  $entityId
     * @param  int|string|null  $companyId
     * @param  mixed  $oldData
     * @param  mixed  $newData
     */
    public static function log(
        $action,
        $entityType,
        $entityId = null,
        $companyId = null,
        $oldData = null,
        $newData = null,
        ?int $userId = null
    ): ?ActivityLog {
        $userId = $userId ?? Auth::id();
        if (! $userId) {
            $userId = DB::table('users')->where('role', 'ADMIN')->value('id') ?: 1;
        }

        if (! $companyId) {
            if (strcasecmp($entityType, 'Company') === 0 && $entityId) {
                $companyId = (int) $entityId;
            } else {
                $companyId = request()->integer('company_id')
                    ?: optional(Auth::user())->company_id
                    ?: DB::table('companies')->where('status', 'ACTIVE')->value('id')
                    ?: DB::table('companies')->value('id');
            }
        }

        if (! $companyId) {
            return null;
        }

        if (is_object($oldData) && method_exists($oldData, 'toArray')) {
            $oldData = $oldData->toArray();
        } elseif (is_object($oldData)) {
            $oldData = (array) $oldData;
        }

        if (is_object($newData) && method_exists($newData, 'toArray')) {
            $newData = $newData->toArray();
        } elseif (is_object($newData)) {
            $newData = (array) $newData;
        }

        return ActivityLog::create([
            'user_id' => $userId,
            'company_id' => (int) $companyId,
            'entity_type' => $entityType,
            'entity_id' => (int) ($entityId ?: 0),
            'action' => $action,
            'old_data' => $oldData,
            'new_data' => $newData,
            'created_at' => now(),
        ]);
    }
}
