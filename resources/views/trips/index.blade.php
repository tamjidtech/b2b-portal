<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">Trips</h2>
            <a href="{{ route('trips.create') }}" class="bg-purple-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-purple-700">+ Add Trip</a>
        </div>
    </x-slot>
    <div class="py-8 max-w-6xl mx-auto px-4">
        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded">{{ session('success') }}</div>
        @endif
        <div class="bg-white shadow rounded-xl overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Trip</th>
                        <th class="px-4 py-3">Direction</th>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Weight kg</th>
                        <th class="px-4 py-3 text-right">Trip Cost</th>
                        <th class="px-4 py-3 text-right">Goods Cost</th>
                        <th class="px-4 py-3 text-right">Total Invested</th>
                        <th class="px-4 py-3 text-right">Purchases</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($trips as $t)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium">
                            <a href="{{ route('trips.show', $t) }}" class="text-blue-600 hover:underline">{{ $t->name }}</a>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs {{ $t->direction === 'SA_TO_BD' ? 'bg-orange-100 text-orange-700' : 'bg-green-100 text-green-700' }}">
                                {{ $t->direction === 'SA_TO_BD' ? 'SA→BD' : 'BD→SA' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">{{ $t->trip_date->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded text-xs font-medium {{ $t->status === 'completed' ? 'bg-green-100 text-green-700' : ($t->status === 'planned' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500') }}">
                                {{ ucfirst($t->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">{{ $t->luggage_weight_kg }}</td>
                        <td class="px-4 py-3 text-right">৳{{ number_format($t->totalTripCost()) }}</td>
                        <td class="px-4 py-3 text-right">৳{{ number_format($t->totalGoodsCost()) }}</td>
                        <td class="px-4 py-3 text-right font-semibold">৳{{ number_format($t->totalInvested()) }}</td>
                        <td class="px-4 py-3 text-right">{{ $t->purchases_count }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('trips.edit', $t) }}" class="text-indigo-600 hover:underline text-xs">Edit</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="px-4 py-8 text-center text-gray-400">No trips. <a href="{{ route('trips.create') }}" class="text-blue-600 underline">Add your first trip</a>.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $trips->links() }}</div>
    </div>
</x-app-layout>
