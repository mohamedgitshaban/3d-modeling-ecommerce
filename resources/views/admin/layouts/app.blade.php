<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') — FixtureCraft</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-100 text-stone-900 antialiased flex">

    <aside class="w-60 bg-stone-900 text-stone-300 min-h-screen p-5 flex-shrink-0">
        <div class="text-white font-serif text-xl mb-8"><a href="{{ route('admin.dashboard') }}">FixtureCraft Admin</a></div>
        <nav class="space-y-1 text-sm">
            <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded hover:bg-stone-800">Dashboard</a>
            <div class="pt-4 pb-1 text-xs uppercase tracking-wide text-stone-500">Catalog</div>
            <a href="{{ route('admin.categories.index') }}" class="block px-3 py-2 rounded hover:bg-stone-800">Categories</a>
            <a href="{{ route('admin.brands.index') }}" class="block px-3 py-2 rounded hover:bg-stone-800">Brands</a>
            <a href="{{ route('admin.products.index') }}" class="block px-3 py-2 rounded hover:bg-stone-800">Products &amp; 3D Models</a>
            <a href="{{ route('admin.collections.index') }}" class="block px-3 py-2 rounded hover:bg-stone-800">Collections</a>
            <div class="pt-4 pb-1 text-xs uppercase tracking-wide text-stone-500">Inventory</div>
            <a href="{{ route('admin.stock.index') }}" class="block px-3 py-2 rounded hover:bg-stone-800">Stock</a>
            <div class="pt-4 pb-1 text-xs uppercase tracking-wide text-stone-500">Marketing</div>
            <a href="{{ route('admin.coupons.index') }}" class="block px-3 py-2 rounded hover:bg-stone-800">Coupons</a>
            <a href="{{ route('admin.offers.index') }}" class="block px-3 py-2 rounded hover:bg-stone-800">Offers</a>
            <div class="pt-4 pb-1 text-xs uppercase tracking-wide text-stone-500">Sales</div>
            <a href="{{ route('admin.orders.index') }}" class="block px-3 py-2 rounded hover:bg-stone-800">Orders &amp; Tracking</a>
            <div class="pt-4 pb-1 text-xs uppercase tracking-wide text-stone-500">Settings</div>
            <a href="{{ route('admin.shipping.index') }}" class="block px-3 py-2 rounded hover:bg-stone-800">Shipping</a>
            <a href="{{ route('admin.store-locations.index') }}" class="block px-3 py-2 rounded hover:bg-stone-800">Store Locations</a>
        </nav>
        <form method="POST" action="{{ route('logout') }}" class="mt-8">
            @csrf
            <button class="text-sm text-stone-400 hover:text-white">Logout</button>
        </form>
    </aside>

    <main class="flex-1 p-8">
        @if (session('status'))
            <div class="mb-6 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-6 rounded-md bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <h1 class="text-2xl font-semibold mb-6">@yield('heading', 'Dashboard')</h1>

        @yield('content')
    </main>
</body>
</html>
