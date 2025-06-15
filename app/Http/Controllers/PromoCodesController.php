<?php

namespace App\Http\Controllers;

use App\Models\PromoCode;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\PromoCodeRequest;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;
use App\Enums\PromoCodeType;
use App\Enums\UserRole;
use App\Http\Resources\PromoCodeResource;


class PromoCodesController extends Controller
{
    #use AuthorizesRequests;

    public function store(Request $request)
    {
        $validator =  Validator::make($request->all(), [
            'code' => 'nullable|string|unique:promo_codes,code',
            'type' => ['required', new Enum(PromoCodeType::class)],
            'amount' => 'required|numeric|min:0',
            'max_usage' => 'nullable|integer|min:1',
            'user_max_usage' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date|after:now',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            $errorMessage = $validator->errors()->first();
            $response = [
                'status'  => false,
                'message' => $errorMessage,
            ];
            return response()->json($response, 401);
        }

        $invalidUsers = User::whereIn('id', $request->user_ids)
                    ->where('role', '!=', UserRole::USER)
                    ->pluck('id')
                    ->toArray();

        if (!empty($invalidUsers)) {
            return response()->json([
                'message' => 'Some selected users are not regular users.',
                    'invalid_user_ids' => $invalidUsers
            ], 401);
        }

        $promo_code = PromoCode::create([
            "code" => $request->code ?? strtoupper(Str::random(5)),
            "type" => $request->type,
            "amount" => $request->amount,
            "max_usage" => $request->max_usage,
            "user_max_usage" => $request->user_max_usage,
            "expires_at" => $request->expires_at,
        ]);

        if (!empty($request->user_ids)) {
            $promo_code->users()->sync($request->user_ids);
        }

        return response()->json([
            'message' => 'Promo code created successfully',
            'data' => new PromoCodeResource($promo_code),
        ], 201);

    }

    public function redeem(Request $request)
    {
        return response()->json([
            'message' => "redeem Promo Code",
            'data' => []
        ], 200);
    }

}
