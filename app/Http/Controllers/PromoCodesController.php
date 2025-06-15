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
use Illuminate\Validation\Rule;


class PromoCodesController extends Controller
{
    #use AuthorizesRequests;

    public function store(Request $request)
    {
        $validator =  Validator::make($request->all(), [
            'code' => 'nullable|string|min:5|unique:promo_codes,code',
            'type' => ['required', new Enum(PromoCodeType::class)],
            'amount' => 'required|numeric|min:0.1',
            'max_usage' => 'nullable|integer|min:1',
            'user_max_usage' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date|after:now',
            'user_ids' => 'array',
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
            "code" => $request->code ? strtoupper($request->code): strtoupper(Str::random(5)),
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
        $validator =  Validator::make($request->all(), [
            'price' => 'required|numeric|min:1',
            'code' => ['required', Rule::exists('promo_codes', 'code')],
        ]);

        if ($validator->fails()) {
            $errorMessage = $validator->errors()->first();
            $response = [
                'status'  => false,
                'message' => $errorMessage,
            ];
            return response()->json($response, 401);
        }

        $code = PromoCode::where("code", $request->code)->first();
        $auth_user = auth()->user();
        $code_user = $code->users()->where('user_id', $auth_user->id)->first();
        //dd($code_user);

        //Check expiry
        if($code->expires_at < now()) {
            $response = [
                'status'  => false,
                'message' => "The selected code is expired",
            ];
            return response()->json($response, 401);
        }

        //Check is available for the requested user
        if (!$code_user){
            $response = [
                'status'  => false,
                'message' => "The selected code invalid for the current user",
            ];
            return response()->json($response, 401);
        }

        //Check number of usages
        if ($code->usage >= $code->max_usage) {
            $response = [
                'status'  => false,
                'message' => "The selected code exceeded max usage",
            ];
            return response()->json($response, 401);
        }

        //Check number of usages by the requested user
        $current_used = $code_user->pivot->times_redeemed;
        if($current_used >= $code->user_max_usage) {
            $response = [
                'status'  => false,
                'message' => "The selected code exceeded number of usage for the current user",
            ];
            return response()->json($response, 401);
        }
        
        $code->users()->updateExistingPivot($auth_user->id, ['times_redeemed' => $current_used + 1]);
        $discount = $code->calculate_discount($request->price);
        return response()->json([
            'message' => "redeem Promo Code",
            'data' => [
                "price" => $request->price,
                "promocode_discounted_amount" => $discount,
                "final_price" => round($request->price - $discount, 2)
            ]
        ], 200);
    }

}
