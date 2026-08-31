@extends('storefront.layouts.app')

@section('title', 'Register — FixtureCraft')

@section('content')
    <div class="max-w-md mx-auto px-4 py-16">
        <h1 class="text-2xl font-serif font-semibold mb-8">Create an Account</h1>
        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Name</label>
                <input type="text" name="name" required value="{{ old('name') }}" class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Email</label>
                <input type="email" name="email" required value="{{ old('email') }}" class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Password</label>
                <input type="password" name="password" required class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Confirm Password</label>
                <input type="password" name="password_confirmation" required class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
            </div>
            <button class="w-full bg-stone-900 hover:bg-stone-800 text-white font-semibold py-3 rounded-md">Register</button>
        </form>
        <p class="text-sm text-stone-500 mt-4">Already have an account? <a href="{{ route('login') }}" class="text-amber-700 hover:underline">Login</a></p>
    </div>
@endsection
