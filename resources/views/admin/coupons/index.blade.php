@extends('admin.layouts.app')

@section('title', 'Coupons')
@section('heading', 'Coupons')

@section('content')
    <a href="{{ route('admin.coupons.create') }}" class="inline-block mb-4 bg-stone-900 text-white text-sm font-medium px-4 py-2 rounded-md">+ New Coupon</a>

    <div class="bg-white rounded-lg border border-stone-200">
        <table class="w-full text-sm">
            <thead class="text-left text-stone-500 border-b border-stone-100">
                <tr><th class="p-3">Code</th><th class="p-3">Type</th><th class="p-3">Value</th><th class="p-3">Used</th><th class="p-3">Active</th><th class="p-3"></th></tr>
            </thead>
            <tbody>
                @foreach ($coupons as $coupon)
                    <tr class="border-b border-stone-50">
                        <td class="p-3 font-medium">{{ $coupon->code }}</td>
                        <td class="p-3">{{ $coupon->type }}</td>
                        <td class="p-3">{{ $coupon->value }}</td>
                        <td class="p-3">{{ $coupon->times_used }}{{ $coupon->usage_limit ? ' / '.$coupon->usage_limit : '' }}</td>
                        <td class="p-3">{{ $coupon->is_active ? 'Yes' : 'No' }}</td>
                        <td class="p-3 text-right space-x-2">
                            <a href="{{ route('admin.coupons.edit', $coupon) }}" class="text-amber-700 hover:underline">Edit</a>
                            <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" class="inline" onsubmit="return confirm('Delete this coupon?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $coupons->links() }}</div>
@endsection
