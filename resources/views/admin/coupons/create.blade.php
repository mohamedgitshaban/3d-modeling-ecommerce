@extends('admin.layouts.app')

@section('title', 'New Coupon')
@section('heading', 'New Coupon')

@section('content')
    <form method="POST" action="{{ route('admin.coupons.store') }}" class="bg-white border border-stone-200 rounded-lg p-6 max-w-2xl">
        @csrf
        @include('admin.coupons._form')
    </form>
@endsection
