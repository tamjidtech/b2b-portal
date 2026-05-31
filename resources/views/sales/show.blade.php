<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">Sale: {{ $sale->product->name ?? 'Unknown' }}</h2>
            <a href="{{ route('sales.edit', $sale) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm">Edit</a>
        </div>
    </x-slot>
    <div class="py-8 max-w-3xl mx-auto px-4 space-y-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white border rounded-xl p-4"><p class="text-xs text-gray-500">Quantity</p><p class="text-xl font-bold">{{ $sale->quantity }}</p></div>
            <div class="bg-white border rounded-xl p-4"><p class="text-xs text-gray-500">Unit Price</p><p class="text-xl font-bold">৳{{ number_format($sale->unit_price_bdt) }}</p></div>
            <div class="bg-white border rounded-xl p-4"><p class="text-xs text-gray-500">Gross Revenue</p><p class="text-xl font-bold text-blue-700">৳{{ number_format($sale->grossRevenue()) }}</p></div>
            <div class="bg-white border rounded-xl p-4"><p class="text-xs text-gray-500">Net Revenue</p><p class="text-xl font-bold">৳{{ number_format($sale->netRevenue()) }}</p></div>
            <div class="bg-white border rounded-xl p-4"><p class="text-xs text-gray-500">Platform Fee</p><p class="text-xl font-bold">৳{{ number_format($sale->platform_fee_bdt) }}</p></div>
            <div class="bg-white border rounded-xl p-4"><p class="text-xs text-gray-500">Delivery Cost</p><p class="text-xl font-bold">৳{{ number_format($sale->delivery_cost_bdt) }}</p></div>
            <div class="bg-white border rounded-xl p-4"><p class="text-xs text-gray-500">Cost of Goods</p><p class="text-xl font-bold text-red-600">৳{{ number_format($sale->costOfGoods()) }}</p></div>
            <div class="bg-white border rounded-xl p-4 {{ $sale->netProfit() >= 0 ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }}">
                <p class="text-xs text-gray-500">Net Profit</p>
                <p class="text-xl font-bold {{ $sale->netProfit() >= 0 ? 'text-green-700' : 'text-red-600' }}">৳{{ number_format($sale->netProfit()) }}</p>
            </div>
        </div>
        <div class="bg-white shadow rounded-xl p-5 text-sm grid md:grid-cols-2 gap-3">
            <div><span class="text-gray-500">Date:</span> {{ $sale->sale_date->format('d M Y') }}</div>
            <div><span class="text-gray-500">Platform:</span> {{ $sale->platform ?? '—' }}</div>
            <div><span class="text-gray-500">Buyer Ref:</span> {{ $sale->buyer_ref ?? '—' }}</div>
            <div><span class="text-gray-500">Linked Purchase:</span>
                @if($sale->purchase)
                    <a href="{{ route('purchases.show', $sale->purchase) }}" class="text-blue-600 hover:underline">View Purchase</a>
                @else — @endif
            </div>
            @if($sale->notes)<div class="md:col-span-2"><span class="text-gray-500">Notes:</span> {{ $sale->notes }}</div>@endif
        </div>
    </div>
</x-app-layout>
