<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use App\Enums\PromoCodeType;

class PromoCode extends Model
{
    protected $fillable = [
        "code", "type", "amount", "usage", "max_usage", "user_max_usage", "expires_at"
    ];

    protected $dates = ['expires_at'];

    protected function casts(): array
    {
        return [
            'type' => PromoCodeType::class,
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('times_redeemed');
    }

    public function calculate_discount($price) {
        if($this->type == PromoCodeType::VALUE) {
            return $this->amount;
        }else {
            return round($price * ($this->amount / 100), 2);
        }
    }
}
