<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UsersController;
use App\Http\Controllers\PromoCodesController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/test', function () {
    return response()->json(['message' => 'Hello, API']);
});

Route::post("/register", [UsersController::class, 'register']);
Route::post("/login", [UsersController::class, 'login']);

Route::group(["middleware" => ["auth:sanctum"]],
    function() {
        Route::get('/profile', [UsersController::class, 'profile']);
        Route::get('/logout', [UsersController::class, 'logout']);
        Route::post("/promo-codes/create", 
            [PromoCodesController::class, 'store'])->middleware('can:create,App\Models\PromoCode');
        Route::post("/promo-codes/redeem",
            [PromoCodesController::class, 'redeem'])->middleware('can:redeem,App\Models\PromoCode');
    }
);


