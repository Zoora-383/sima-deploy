<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'fullname'   => $this->userProfile->fullname ?? null,
            'phone'      => $this->userProfile->phone ?? null,
            'location'   => $this->userProfile->location ?? null,
            'avatar' => $this->avatar_url ? asset('storage/' . $this->avatar_url) : null,
        ];
    }
}
