<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid'     => $this->uuid,
            'role'     => $this->role ? $this->role->name : null,
            'email'    => $this->email,
            'profile'    => [
                'username'   => $this->userProfile->username ?? null,
                'full_name'  => $this->userProfile->fullname ?? null,
                'phone'      => $this->userProfile->phone ?? null,
                'location'   => $this->userProfile->location ?? null,
                'avatar_url' => $this->userProfile->avatar_url ?? null,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
