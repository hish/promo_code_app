<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromoCodeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            "type" => $this->type,
            "amount" => $this->amount,
            "max_usage" => $this->max_usage,
            "user_max_usage" => $this->user_max_usage,
            "expires_at" => $this->expires_at,
            "user_ids" => $this->users->pluck('id'),
        ];
    }
}
