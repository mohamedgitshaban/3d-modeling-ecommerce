@extends('storefront.layouts.app')

@section('title', 'Login — FixtureCraft')

@section('content')
    <div class="max-w-md mx-auto px-4 py-16">
        <h1 class="text-2xl font-serif font-semibold mb-8">Login</h1>
        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Email</label>
                <input type="email" name="email" required value="{{ old('email') }}" class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Password</label>
                <input type="password" name="password" required class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
            </div>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember"> Remember me</label>
            <button class="w-full bg-stone-900 hover:bg-stone-800 text-white font-semibold py-3 rounded-md">Login</button>
        </form>
        <p class="text-sm text-stone-500 mt-4">No account? <a href="{{ route('register') }}" class="text-amber-700 hover:underline">Register</a></p>
    </div>
@endsection
