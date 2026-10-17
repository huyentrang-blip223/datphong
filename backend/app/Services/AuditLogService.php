<?php
namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;

class AuditLogService
{
    public function record(?User $actor, string $action, string $entity, ?int $entityId = null, ?array $before = null, ?array $after = null): AuditLog
    {
        return AuditLog::create([
            'actor_id' => $actor?->id,
            'action' => $action,
            'entity' => $entity,
            'entity_id' => $entityId,
            'before_json' => $before,
            'after_json' => $after,
            'ip_address' => request()?->ip(),
        ]);
    }
}
