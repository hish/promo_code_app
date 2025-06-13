<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PromoCodeController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/test', function () {
    return response()->json(['message' => 'Hello, API']);
});

Route::post("/promo-codes",[PromoCodeController::class, 'store']);