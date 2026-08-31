@extends('storefront.layouts.app')

@section('title', 'Track Your Order — FixtureCraft')

@section('content')
    <div class="max-w-md mx-auto px-4 py-16">
        <h1 class="text-2xl font-serif font-semibold mb-2">Track Your Order</h1>
        <p class="text-stone-600 mb-8">Enter your order number and the email you used at checkout.</p>

        <form method="POST" action="{{ route('orders.track.lookup') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Order Number</label>
                <input type="text" name="order_number" required placeholder="ORD-XXXXXXXXXX" value="{{ old('order_number') }}" class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Email</label>
                <input type="email" name="email" required value="{{ old('email') }}" class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
            </div>
            <button class="w-full bg-stone-900 hover:bg-stone-800 text-white font-semibold py-3 rounded-md">Track Order</button>
        </form>
    </div>
@endsection
