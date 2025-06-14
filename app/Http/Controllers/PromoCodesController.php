<?php

namespace App\Http\Controllers;

use App\Models\PromoCode;
use Illuminate\Http\Request;
use App\Http\Requests\PromoCodeRequest;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class PromoCodesController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request)
    {
        // $data = $request->validated();

        // $data['code'] = $data['code'] ?? strtoupper(Str::random(5));

        // $promo_code = PromoCode::create($data);

        // if (!empty($data['user_ids'])) {
        //     $promo_code->users()->sync($data['user_ids']);
        // }

        return response()->json([
            'message' => 'Promo code created successfully',
            'data' => [],
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
