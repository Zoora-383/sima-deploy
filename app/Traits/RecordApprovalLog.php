<?php

namespace App\Traits;

use App\Models\ApprovalLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

trait RecordApprovalLog
{
    /**
     * Record an approval log for a model.
     *
     * @param Model $model
     * @param string $statusFrom
     * @param string $statusTo
     * @param string|null $note
     * @param int $userId
     * @return ApprovalLog
     */
    public function recordLog(Model $model, string $statusFrom, string $statusTo, ?string $note, int $userId): ApprovalLog
    {
        // 1. Fetch user to generate actor snapshot
        $user = User::with('role')->find($userId);
        $actorSnapshot = null;
        if ($user) {
            $actorSnapshot = [
                'user_id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role->name ?? 'unknown',
            ];
        }

        // 2. Generate target model data snapshot
        $dataSnapshot = $model->toArray();

        return $model->approvalLogs()->create([
            'user_id' => $userId,
            'status_from' => $statusFrom,
            'status_to' => $statusTo,
            'note' => $note,
            'actor_snapshot' => $actorSnapshot,
            'data_snapshot' => $dataSnapshot,
        ]);
    }
}
