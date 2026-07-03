<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $uuid
 * @property int $user_id
 * @property string $title
 * @property string $message
 * @property \Illuminate\Support\Carbon|null $read_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @property-read bool $is_read
 */
#[Fillable(['uuid', 'user_id', 'title', 'message', 'read_at'])]
class Notification extends Model
{
    use SoftDeletes;

    protected $table = 'notifications';

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Send notification to a specific user.
     */
    public static function sendToUser(int $userId, string $title, string $message): self
    {
        return self::create([
            'user_id' => $userId,
            'title'   => $title,
            'message' => $message,
            'read_at' => null,
        ]);
    }

    /**
     * Send notification to all users with a specific role.
     */
    public static function sendToRole(string $roleName, string $title, string $message): void
    {
        $users = User::whereHas('role', function ($query) use ($roleName) {
            $query->where('name', $roleName);
        })->get();

        foreach ($users as $user) {
            self::sendToUser($user->id, $title, $message);
        }
    }

    public function getIsReadAttribute(): bool
    {
        return !is_null($this->read_at);
    }

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }
}
