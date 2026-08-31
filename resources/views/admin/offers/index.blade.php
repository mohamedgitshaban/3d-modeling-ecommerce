@extends('admin.layouts.app')

@section('title', 'Offers')
@section('heading', 'Offers & Flash Sales')

@section('content')
    <a href="{{ route('admin.offers.create') }}" class="inline-block mb-4 bg-stone-900 text-white text-sm font-medium px-4 py-2 rounded-md">+ New Offer</a>

    <div class="bg-white rounded-lg border border-stone-200">
        <table class="w-full text-sm">
            <thead class="text-left text-stone-500 border-b border-stone-100">
                <tr><th class="p-3">Name</th><th class="p-3">Type</th><th class="p-3">Value</th><th class="p-3">Target</th><th class="p-3">Window</th><th class="p-3">Active</th><th class="p-3"></th></tr>
            </thead>
            <tbody>
                @foreach ($offers as $offer)
                    <tr class="border-b border-stone-50">
                        <td class="p-3">{{ $offer->name }}</td>
                        <td class="p-3">{{ $offer->type }}</td>
                        <td class="p-3">{{ $offer->value }}</td>
                        <td class="p-3">{{ $offer->target_type }} #{{ $offer->target_id }}</td>
                        <td class="p-3 text-xs">{{ $offer->starts_at->format('M j') }} – {{ $offer->ends_at->format('M j') }}</td>
                        <td class="p-3">{{ $offer->is_active ? 'Yes' : 'No' }}</td>
                        <td class="p-3 text-right">
                            <form method="POST" action="{{ route('admin.offers.destroy', $offer) }}" onsubmit="return confirm('Delete this offer?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline text-xs">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $offers->links() }}</div>
@endsection
