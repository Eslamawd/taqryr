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
            'id'    => $this->id,
            'name'  => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'verified' => $this->hasVerifiedEmail(),    
            'phone_verified' => $this->hasVerifiedPhone(),
            'role'  => $this->getRoleNames()->first() ?: 'user',
            'balance' => $this->balanceInt / 100,
            'subscripe'=> $this->subscripe ?? null,

        ];
    }
}
