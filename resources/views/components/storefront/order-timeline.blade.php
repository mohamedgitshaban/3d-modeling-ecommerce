@props(['order', 'timeline'])

<div>
    @if ($order->isCancelledOrRefunded())
        <div class="rounded-md bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm mb-6">
            This order was {{ $order->status }}.
        </div>
    @else
        <ol class="flex items-center w-full mb-8" id="order-timeline">
            @foreach ($timeline as $i => $step)
                <li class="flex-1 flex items-center {{ $i === count($timeline) - 1 ? 'flex-none' : '' }}">
                    <div class="flex flex-col items-center text-center flex-shrink-0 w-24">
                        <div class="h-9 w-9 rounded-full flex items-center justify-center text-white text-sm font-semibold {{ $step['completed'] ? 'bg-emerald-600' : 'bg-stone-300' }}" data-status="{{ $step['status'] }}">
                            @if ($step['completed'])
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.4 7.4a1 1 0 01-1.4 0L3.3 9.5a1 1 0 111.4-1.4l3.6 3.6 6.7-6.7a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
                            @else
                                {{ $i + 1 }}
                            @endif
                        </div>
                        <span class="text-xs mt-2 font-medium {{ $step['completed'] ? 'text-stone-800' : 'text-stone-400' }}">{{ $step['label'] }}</span>
                        @if ($step['at'])
                            <span class="text-[10px] text-stone-400">{{ $step['at']->format('M j') }}</span>
                        @endif
                    </div>
                    @if (! $loop->last)
                        <div class="flex-1 h-0.5 {{ $step['completed'] ? 'bg-emerald-600' : 'bg-stone-200' }}"></div>
                    @endif
                </li>
            @endforeach
        </ol>
    @endif

    @if ($order->tracking_number)
        <div class="bg-white border border-stone-200 rounded-lg p-4 flex items-center justify-between text-sm mb-6">
            <div>
                <div class="text-stone-500">Carrier</div>
                <div class="font-medium">{{ $order->carrier }}</div>
            </div>
            <div>
                <div class="text-stone-500">Tracking Number</div>
                <div class="font-medium">{{ $order->tracking_number }}</div>
            </div>
            @if ($order->carrier_tracking_url)
                <a href="{{ $order->carrier_tracking_url }}" target="_blank" class="text-amber-700 hover:text-amber-800 font-medium">Track with carrier →</a>
            @endif
        </div>
    @endif

    @if ($order->estimated_delivery_at)
        <p class="text-sm text-stone-600">Estimated delivery: <span class="font-medium">{{ $order->estimated_delivery_at->format('F j, Y') }}</span></p>
    @endif
</div>
