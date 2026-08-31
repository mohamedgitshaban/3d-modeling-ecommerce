@props(['trail'])

<nav class="text-sm text-stone-500 flex flex-wrap items-center gap-1">
    <a href="{{ route('home') }}" class="hover:text-amber-700">Home</a>
    @foreach ($trail as $node)
        <span class="mx-1">/</span>
        @if ($node instanceof \App\Models\Category)
            <a href="{{ route('categories.show', $node) }}" class="hover:text-amber-700">{{ $node->name }}</a>
        @elseif ($node instanceof \App\Models\Product)
            <span class="text-stone-700">{{ $node->name }}</span>
        @else
            <span class="text-stone-700">{{ $node->name ?? $node }}</span>
        @endif
    @endforeach
</nav>
