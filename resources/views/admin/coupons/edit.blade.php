@extends('admin.layouts.app')

@section('title', 'Edit Coupon')
@section('heading', 'Edit Coupon: '.$coupon->code)

@section('content')
    <form method="POST" action="{{ route('admin.coupons.update', $coupon) }}" class="bg-white border border-stone-200 rounded-lg p-6 max-w-2xl">
        @csrf @method('PUT')
        @include('admin.coupons._form')
    </form>
@endsection
