<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'FixtureCraft — Bath & Kitchen Fixtures')</title>
    <meta name="description" content="@yield('meta_description', 'Faucets, vanities, mirrors and fixtures — shop by finish, size, and collection. View every product in interactive 3D.')">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script type="module" src="https://cdnjs.cloudflare.com/ajax/libs/model-viewer/3.5.0/model-viewer.min.js"></script>
    @stack('head')
</head>
<body class="min-h-screen bg-stone-50 text-stone-900 antialiased flex flex-col">

    <div class="bg-stone-900 text-stone-100 text-xs">
        <div class="max-w-7xl mx-auto px-4 py-2 flex items-center justify-between">
            <span>Free shipping on orders over $500 · Financing available</span>
            <a href="{{ route('store-locator.index') }}" class="hover:underline">Find a Store</a>
        </div>
    </div>

    <header class="bg-white border-b border-stone-200 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 py-4 flex items-center gap-8">
            <a href="{{ route('home') }}" class="text-2xl font-serif font-semibold tracking-tight text-stone-900">FixtureCraft</a>

            <nav class="hidden md:flex items-center gap-6 text-sm font-medium text-stone-700">
                @foreach (\App\Models\Category::whereNull('parent_id')->where('is_active', true)->orderBy('sort_order')->get() as $topCategory)
                    <a href="{{ route('categories.show', $topCategory) }}" class="hover:text-amber-700 transition">{{ $topCategory->name }}</a>
                @endforeach
                <a href="{{ route('orders.track.create') }}" class="hover:text-amber-700 transition">Track Order</a>
            </nav>

            <div class="ml-auto flex items-center gap-5 text-sm">
                @auth
                    <a href="{{ route('account.orders.index') }}" class="hover:text-amber-700">My Orders</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="hover:text-amber-700">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="hover:text-amber-700">Login</a>
                @endauth

                <a href="{{ route('cart.index') }}" class="relative flex items-center gap-1 font-medium">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.29 2.29a1 1 0 00.7 1.71H17m-9-1a1 1 0 102 0 1 1 0 00-2 0zm9 0a1 1 0 102 0 1 1 0 00-2 0z"/></svg>
                    Cart
                </a>
            </div>
        </div>
    </header>

    @if (session('status'))
        <div class="max-w-7xl mx-auto px-4 mt-4 w-full">
            <div class="rounded-md bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">{{ session('status') }}</div>
        </div>
    @endif
    @if ($errors->any())
        <div class="max-w-7xl mx-auto px-4 mt-4 w-full">
            <div class="rounded-md bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="bg-stone-900 text-stone-300 mt-16">
        <div class="max-w-7xl mx-auto px-4 py-12 grid grid-cols-1 md:grid-cols-4 gap-8 text-sm">
            <div>
                <div class="text-white font-serif text-xl mb-3">FixtureCraft</div>
                <p class="text-stone-400">Bath & kitchen fixtures with true-to-life 3D previews, curated collections, and fast shipping.</p>
            </div>
            <div>
                <div class="text-white font-semibold mb-3">Shop</div>
                <ul class="space-y-2 text-stone-400">
                    @foreach (\App\Models\Category::whereNull('parent_id')->where('is_active', true)->take(5)->get() as $cat)
                        <li><a href="{{ route('categories.show', $cat) }}" class="hover:text-white">{{ $cat->name }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div>
                <div class="text-white font-semibold mb-3">Support</div>
                <ul class="space-y-2 text-stone-400">
                    <li><a href="{{ route('orders.track.create') }}" class="hover:text-white">Track your order</a></li>
                    <li><a href="{{ route('store-locator.index') }}" class="hover:text-white">Find a store</a></li>
                </ul>
            </div>
            <div>
                <div class="text-white font-semibold mb-3">Newsletter</div>
                <p class="text-stone-400">Sign up for offers and new arrivals.</p>
            </div>
        </div>
        <div class="border-t border-stone-800 text-center text-xs text-stone-500 py-4">&copy; {{ date('Y') }} FixtureCraft. All rights reserved.</div>
    </footer>
</body>
</html>
