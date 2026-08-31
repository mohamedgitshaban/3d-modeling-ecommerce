@props(['group', 'items'])

<div class="border-t border-stone-200 py-6">
    <h3 class="text-lg font-serif font-semibold text-stone-900 mb-4">{{ $group->label }}</h3>

    @if ($group->type === 'key_value')
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-3">
            @foreach ($items as $item)
                <div class="flex justify-between border-b border-stone-100 pb-2 text-sm">
                    <dt class="text-stone-500">{{ $item['attribute']->label }}:</dt>
                    <dd class="font-medium text-stone-800">{{ $item['value'] }}</dd>
                </div>
            @endforeach
        </dl>
    @elseif ($group->type === 'badge_list')
        <div class="flex flex-wrap gap-2">
            @foreach ($items as $item)
                @foreach (preg_split('/\r\n|\r|\n|,/', trim($item['value'])) as $badge)
                    @continue(trim($badge) === '')
                    <span class="inline-flex items-center gap-1 bg-stone-100 text-stone-700 text-xs font-medium px-3 py-1.5 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-emerald-600" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.4 7.4a1 1 0 01-1.4 0L3.3 9.5a1 1 0 111.4-1.4l3.6 3.6 6.7-6.7a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
                        {{ trim($badge) }}
                    </span>
                @endforeach
            @endforeach
        </div>
    @elseif ($group->type === 'file_list')
        <ul class="space-y-2">
            @foreach ($items as $item)
                <li>
                    <a href="{{ $item['value'] }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 text-amber-700 hover:text-amber-800 text-sm font-medium">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6z" clip-rule="evenodd"/></svg>
                        {{ $item['attribute']->label }}
                    </a>
                </li>
            @endforeach
        </ul>
    @else
        {{-- richtext / bullet_list: auto-detect multi-line values as bullets --}}
        @foreach ($items as $item)
            @php $lines = preg_split('/\r\n|\r|\n/', trim($item['value'])); @endphp
            @if (count($lines) > 1)
                <ul class="list-disc list-inside space-y-1 text-stone-700 mb-4">
                    @foreach ($lines as $line)
                        @continue(trim($line) === '')
                        <li>{{ $line }}</li>
                    @endforeach
                </ul>
            @else
                <p class="text-stone-700 leading-relaxed mb-4">{{ $item['value'] }}</p>
            @endif
        @endforeach
    @endif
</div>
