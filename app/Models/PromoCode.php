<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PromoCode extends Model
{
    protected $fillable = [
        "code", "type", "amount", "usage", "max_usage", "user_max_usage", "expires_at"
    ];

    protected $dates = ['expires_at'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

}
