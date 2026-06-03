<?php

namespace App\Traits;

use App\Models\ApprovalLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
        return $model->approvalLogs()->create([
            'uuid' => Str::uuid()->toString(),
            'user_id' => $userId,
            'status_from' => $statusFrom,
            'status_to' => $statusTo,
            'note' => $note,
        ]);
    }
}
