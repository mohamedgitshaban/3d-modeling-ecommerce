<?php

namespace App\Services\Cart;

use App\Models\Cart;
use Illuminate\Http\Request;

class CartResolver
{
    public function current(Request $request): Cart
    {
        if ($request->user()) {
            return Cart::firstOrCreate(['user_id' => $request->user()->id]);
        }

        $sessionId = $request->session()->get('cart_session_id') ?? $request->session()->getId();
        $request->session()->put('cart_session_id', $sessionId);

        return Cart::firstOrCreate(['session_id' => $sessionId, 'user_id' => null]);
    }
}
