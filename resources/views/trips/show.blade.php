<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">{{ $trip->name }}</h2>
            <a href="{{ route('trips.edit', $trip) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm">Edit</a>
        </div>
    </x-slot>
    <div class="py-8 max-w-5xl mx-auto px-4 space-y-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white border rounded-xl p-4"><p class="text-xs text-gray-500">Direction</p><p class="text-lg font-bold">{{ $trip->direction === 'SA_TO_BD' ? 'SA→BD' : 'BD→SA' }}</p></div>
            <div class="bg-white border rounded-xl p-4"><p class="text-xs text-gray-500">Date</p><p class="text-lg font-bold">{{ $trip->trip_date->format('d M Y') }}</p></div>
            <div class="bg-white border rounded-xl p-4"><p class="text-xs text-gray-500">Status</p><p class="text-lg font-bold">{{ ucfirst($trip->status) }}</p></div>
            <div class="bg-white border rounded-xl p-4"><p class="text-xs text-gray-500">Weight</p><p class="text-lg font-bold">{{ $trip->luggage_weight_kg }} kg</p></div>
            <div class="bg-white border rounded-xl p-4"><p class="text-xs text-gray-500">Flight Cost</p><p class="text-lg font-bold">৳{{ number_format($trip->flight_cost_bdt) }}</p></div>
            <div class="bg-white border rounded-xl p-4"><p class="text-xs text-gray-500">Extra Baggage</p><p class="text-lg font-bold">৳{{ number_format($trip->extra_baggage_cost_bdt) }}</p></div>
            <div class="bg-white border rounded-xl p-4"><p class="text-xs text-gray-500">Other Costs</p><p class="text-lg font-bold">৳{{ number_format($trip->other_cost_bdt) }}</p></div>
            <div class="bg-white border rounded-xl p-4 bg-blue-50 border-blue-200"><p class="text-xs text-gray-500">Total Invested</p><p class="text-lg font-bold text-blue-700">৳{{ number_format($trip->totalInvested()) }}</p></div>
        </div>
        @if($trip->notes)
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-sm">{{ $trip->notes }}</div>
        @endif
        <div class="bg-white shadow rounded-xl p-5">
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-semibold text-gray-700">Purchases on this trip</h3>
                <a href="{{ route('purchases.create') }}" class="text-xs bg-blue-600 text-white px-3 py-1 rounded-lg">+ Add Purchase</a>
            </div>
            <table class="w-full text-sm">
                <thead class="text-xs text-gray-500 border-b">
                    <tr><th class="pb-2 text-left">Product</th><th class="pb-2">Qty</th><th class="pb-2 text-right">Unit Cost</th><th class="pb-2 text-right">Total Cost</th><th class="pb-2 text-right">Remaining</th></tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($trip->purchases as $pu)
                    <tr class="hover:bg-gray-50">
                        <td class="py-2">{{ $pu->product->name ?? '—' }}</td>
                        <td class="py-2 text-center">{{ $pu->quantity }}</td>
                        <td class="py-2 text-right">৳{{ number_format($pu->unit_cost_bdt) }}</td>
                        <td class="py-2 text-right">৳{{ number_format($pu->totalCostBdt()) }}</td>
                        <td class="py-2 text-right font-medium">{{ $pu->unitsRemaining() }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="py-4 text-gray-400 text-center">No purchases linked to this trip</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
