<?php

namespace App\Http\Controllers;

use App\Models\PromoCode;
use Illuminate\Http\Request;
use App\Http\Requests\PromoCodeRequest;
use Illuminate\Support\Str;


class PromoCodeController extends Controller
{

    /**
     * Store a newly created resource in storage.
     */
    public function store(PromoCodeRequest $request)
    {
        $data = $request->validated();

        $data['code'] = $data['code'] ?? strtoupper(Str::random(5));

        $promo_code = PromoCode::create($data);

        if (!empty($data['user_ids'])) {
            $promo_code->users()->sync($data['user_ids']);
        }

        return response()->json([
            'message' => 'Promo code created successfully',
            'data' => $promo_code,
        ], 201);

    }

}
