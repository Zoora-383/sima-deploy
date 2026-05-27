<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid'       => $this->uuid,
            'role'       => $this->role->name ?? null,
            'email'      => $this->email,
            'username'   => $this->username,
            'is_active'  => (bool) $this->is_active,
            'profile'    => [
                'fullname'  => $this->userProfile->fullname ?? null,
                'phone'      => $this->userProfile->phone ?? null,
                'location'   => $this->userProfile->location ?? null,
                'avatar_url' => $this->userProfile->avatar_url ?? null,
            ],
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,
        ];
    }
}