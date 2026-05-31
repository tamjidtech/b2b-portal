<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">Purchase: {{ $purchase->product->name ?? 'Unknown' }}</h2>
            <a href="{{ route('purchases.edit', $purchase) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm">Edit</a>
        </div>
    </x-slot>
    <div class="py-8 max-w-4xl mx-auto px-4 space-y-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white border rounded-xl p-4"><p class="text-xs text-gray-500">Quantity</p><p class="text-xl font-bold">{{ $purchase->quantity }}</p></div>
            <div class="bg-white border rounded-xl p-4"><p class="text-xs text-gray-500">Unit Cost</p><p class="text-xl font-bold">৳{{ number_format($purchase->unit_cost_bdt) }}</p></div>
            <div class="bg-white border rounded-xl p-4"><p class="text-xs text-gray-500">Total All-in Cost</p><p class="text-xl font-bold text-red-700">৳{{ number_format($purchase->totalCostBdt()) }}</p></div>
            <div class="bg-white border rounded-xl p-4"><p class="text-xs text-gray-500">All-in / unit</p><p class="text-xl font-bold text-blue-700">৳{{ number_format($purchase->allInUnitCost()) }}</p></div>
            <div class="bg-white border rounded-xl p-4"><p class="text-xs text-gray-500">Shipping</p><p class="text-xl font-bold">৳{{ number_format($purchase->shipping_cost_bdt) }}</p></div>
            <div class="bg-white border rounded-xl p-4"><p class="text-xs text-gray-500">Customs</p><p class="text-xl font-bold">৳{{ number_format($purchase->customs_cost_bdt) }}</p></div>
            <div class="bg-white border rounded-xl p-4"><p class="text-xs text-gray-500">Other</p><p class="text-xl font-bold">৳{{ number_format($purchase->other_cost_bdt) }}</p></div>
            <div class="bg-white border rounded-xl p-4 {{ $purchase->unitsRemaining() > 0 ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }}">
                <p class="text-xs text-gray-500">Remaining</p>
                <p class="text-xl font-bold">{{ $purchase->unitsRemaining() }} / {{ $purchase->quantity }}</p>
            </div>
        </div>
        <div class="bg-white shadow rounded-xl p-5 text-sm grid md:grid-cols-2 gap-3">
            <div><span class="text-gray-500">Date:</span> {{ $purchase->purchase_date->format('d M Y') }}</div>
            <div><span class="text-gray-500">Trip:</span> {{ $purchase->trip->name ?? '—' }}</div>
            <div><span class="text-gray-500">Invoice Ref:</span> {{ $purchase->invoice_ref ?? '—' }}</div>
            @if($purchase->notes)<div class="md:col-span-2"><span class="text-gray-500">Notes:</span> {{ $purchase->notes }}</div>@endif
        </div>
        <div class="bg-white shadow rounded-xl p-5">
            <h3 class="font-semibold text-gray-700 mb-3">Sales from this purchase</h3>
            <table class="w-full text-sm">
                <thead class="text-xs text-gray-500 border-b">
                    <tr><th class="pb-2 text-left">Date</th><th class="pb-2">Qty</th><th class="pb-2 text-right">Unit Price</th><th class="pb-2 text-right">Revenue</th><th class="pb-2 text-right">Net Profit</th><th class="pb-2">Platform</th></tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($purchase->sales as $s)
                    <tr class="hover:bg-gray-50">
                        <td class="py-2">{{ $s->sale_date->format('d M Y') }}</td>
                        <td class="py-2 text-center">{{ $s->quantity }}</td>
                        <td class="py-2 text-right">৳{{ number_format($s->unit_price_bdt) }}</td>
                        <td class="py-2 text-right">৳{{ number_format($s->grossRevenue()) }}</td>
                        <td class="py-2 text-right font-semibold {{ $s->netProfit() >= 0 ? 'text-green-700' : 'text-red-600' }}">৳{{ number_format($s->netProfit()) }}</td>
                        <td class="py-2 pl-3 text-gray-500">{{ $s->platform ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="py-4 text-gray-400 text-center">No sales yet from this purchase</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
