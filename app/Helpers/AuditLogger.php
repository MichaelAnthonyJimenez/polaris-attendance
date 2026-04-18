<?php

namespace App\Helpers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    public static function log(string $action, ?string $modelType = null, ?int $modelId = null, ?array $oldValues = null, ?array $newValues = null, ?string $description = null): void
    {
        $authUserId = Auth::id();

        // Self-deletes can invalidate the FK before the audit record is written.
        if ($authUserId !== null && ! User::whereKey($authUserId)->exists()) {
            $authUserId = null;
        }

        AuditLog::create([
            'user_id' => $authUserId,
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'description' => $description,
        ]);
    }
}

