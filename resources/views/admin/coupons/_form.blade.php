@php $coupon ??= new \App\Models\Coupon(); @endphp

<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium mb-1">Code</label>
        <input type="text" name="code" required value="{{ old('code', $coupon->code) }}" class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm uppercase">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Type</label>
        <select name="type" class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
            @foreach (['percentage' => 'Percentage', 'fixed_amount' => 'Fixed Amount', 'free_shipping' => 'Free Shipping'] as $value => $label)
                <option value="{{ $value }}" @selected(old('type', $coupon->type) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Value</label>
        <input type="number" step="0.01" name="value" value="{{ old('value', $coupon->value) }}" class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Scope</label>
        <select name="scope" class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
            @foreach (['all', 'category', 'product', 'collection'] as $scope)
                <option value="{{ $scope }}" @selected(old('scope', $coupon->scope) === $scope)>{{ ucfirst($scope) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Min Order Amount</label>
        <input type="number" step="0.01" name="min_order_amount" value="{{ old('min_order_amount', $coupon->min_order_amount) }}" class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Max Discount Amount</label>
        <input type="number" step="0.01" name="max_discount_amount" value="{{ old('max_discount_amount', $coupon->max_discount_amount) }}" class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Usage Limit (total)</label>
        <input type="number" name="usage_limit" value="{{ old('usage_limit', $coupon->usage_limit) }}" class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Usage Limit (per customer)</label>
        <input type="number" name="usage_limit_per_customer" value="{{ old('usage_limit_per_customer', $coupon->usage_limit_per_customer) }}" class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Starts At</label>
        <input type="datetime-local" name="starts_at" value="{{ old('starts_at', $coupon->starts_at?->format('Y-m-d\TH:i')) }}" class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Expires At</label>
        <input type="datetime-local" name="expires_at" value="{{ old('expires_at', $coupon->expires_at?->format('Y-m-d\TH:i')) }}" class="w-full border border-stone-300 rounded-md py-2 px-3 text-sm">
    </div>
</div>
<label class="flex items-center gap-2 text-sm mt-4"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $coupon->is_active ?? true))> Active</label>
<button class="mt-4 bg-stone-900 text-white text-sm font-medium px-4 py-2 rounded-md">Save Coupon</button>
