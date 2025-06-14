<?php

namespace App\Policies;

use App\Models\PromoCode;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Enums\UserRole;

class PromoCodePolicy
{

    public function create(User $user): bool
    {
        return $user->role == UserRole::ADMIN;
    }

    public function redeem(User $user): bool
    {
        return $user->role == UserRole::USER;
    }
}
