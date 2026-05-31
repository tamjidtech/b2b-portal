<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">{{ $product->name }}</h2>
            <a href="{{ route('products.edit', $product) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700">Edit</a>
        </div>
    </x-slot>
    <div class="py-8 max-w-5xl mx-auto px-4 space-y-6">

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">✓ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">✗ {{ session('error') }}</div>
        @endif

        {{-- Summary cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @php
                $cards = [
                    ['label'=>'Purchased',    'value'=>$product->totalUnitsPurchased().' units'],
                    ['label'=>'Sold',         'value'=>$product->totalUnitsSold().' units'],
                    ['label'=>'In Stock',     'value'=>$product->stockInHand().' units'],
                    ['label'=>'Total Revenue','value'=>'৳'.number_format($product->totalRevenue())],
                    ['label'=>'Total Cost',   'value'=>'৳'.number_format($product->totalCost())],
                    ['label'=>'Net Profit',   'value'=>'৳'.number_format($product->totalNetProfit())],
                    ['label'=>'Rating',       'value'=>$product->rating.'★'],
                    ['label'=>'Risk',         'value'=>$product->risk],
                ];
            @endphp
            @foreach($cards as $c)
            <div class="bg-white border rounded-xl p-4 shadow-sm">
                <p class="text-xs text-gray-500 uppercase tracking-wide">{{ $c['label'] }}</p>
                <p class="text-xl font-bold text-gray-800 mt-1">{{ $c['value'] }}</p>
            </div>
            @endforeach
        </div>

        {{-- Info --}}
        <div class="bg-white shadow rounded-xl p-5 text-sm">
            @if ($product->imageSrc())
                <div class="float-right ml-5 mb-3 text-center">
                    <img src="{{ $product->imageSrc() }}" alt="{{ $product->name }}"
                        class="w-36 h-36 object-contain rounded-xl border border-gray-200 bg-gray-50 p-2"
                        onerror="this.parentElement.remove()">
                    @if ($product->url)
                        <form method="POST" action="{{ route('products.fetch-image', $product) }}" class="mt-1">
                            @csrf
                            <button type="submit" class="text-xs text-indigo-500 hover:underline">↻ Refresh Image</button>
                        </form>
                    @endif
                </div>
            @elseif ($product->url)
                <div class="float-right ml-5 mb-3 text-center">
                    <div class="w-36 h-36 rounded-xl border-2 border-dashed border-gray-200 bg-gray-50 flex flex-col items-center justify-center gap-2">
                        <span class="text-3xl font-black text-gray-200">{{ strtoupper(substr($product->name, 0, 1)) }}</span>
                        <form method="POST" action="{{ route('products.fetch-image', $product) }}">
                            @csrf
                            <button type="submit"
                                class="bg-indigo-600 text-white text-xs px-3 py-1.5 rounded-lg hover:bg-indigo-700 font-medium"
                                onclick="this.textContent='Fetching…'; this.disabled=true; this.form.submit();">
                                ⬇ Get Image
                            </button>
                        </form>
                    </div>
                </div>
            @endif
            <div class="grid md:grid-cols-2 gap-4">
            <div><span class="text-gray-500">Pipeline:</span> <strong>{{ $product->pipeline === 'SA_TO_BD' ? 'SA → BD' : 'BD → SA' }}</strong></div>
            <div><span class="text-gray-500">Category:</span> {{ $product->category }}</div>
            <div><span class="text-gray-500">Source:</span> {{ $product->source_market }}</div>
            <div><span class="text-gray-500">Weight:</span> {{ $product->weight_grams }}g</div>
            <div><span class="text-gray-500">Est. Buy:</span> ৳{{ number_format($product->estimated_buy_price_bdt) }}</div>
            <div><span class="text-gray-500">Est. Sell:</span> ৳{{ number_format($product->estimated_sell_price_bdt) }}</div>
            @if($product->url)
            <div class="md:col-span-2"><span class="text-gray-500">URL:</span> <a href="{{ $product->url }}" target="_blank" class="text-blue-600 hover:underline break-all">{{ $product->url }}</a></div>
            @endif
            @if($product->notes)
            <div class="md:col-span-2"><span class="text-gray-500">Notes:</span> {{ $product->notes }}</div>
            @endif
            </div>{{-- end grid --}}
        </div>

        {{-- Purchases --}}
        <div class="bg-white shadow rounded-xl p-5">
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-semibold text-gray-700">Purchases</h3>
                <a href="{{ route('purchases.create', ['product_id' => $product->id]) }}" class="text-xs bg-blue-600 text-white px-3 py-1 rounded-lg">+ Add Purchase</a>
            </div>
            <table class="w-full text-sm">
                <thead class="text-xs text-gray-500 border-b">
                    <tr><th class="pb-2 text-left">Date</th><th class="pb-2">Qty</th><th class="pb-2 text-right">Unit Cost</th><th class="pb-2 text-right">All-in Cost</th><th class="pb-2 text-right">All-in/unit</th><th class="pb-2 text-left">Trip</th></tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($product->purchases as $pu)
                    <tr class="hover:bg-gray-50">
                        <td class="py-2">{{ $pu->purchase_date->format('d M Y') }}</td>
                        <td class="py-2 text-center">{{ $pu->quantity }}</td>
                        <td class="py-2 text-right">৳{{ number_format($pu->unit_cost_bdt) }}</td>
                        <td class="py-2 text-right">৳{{ number_format($pu->totalCostBdt()) }}</td>
                        <td class="py-2 text-right font-medium">৳{{ number_format($pu->allInUnitCost()) }}</td>
                        <td class="py-2 pl-3 text-gray-500">{{ $pu->trip->name ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="py-4 text-gray-400 text-center">No purchases yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Sales --}}
        <div class="bg-white shadow rounded-xl p-5">
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-semibold text-gray-700">Sales</h3>
                <a href="{{ route('sales.create', ['product_id' => $product->id]) }}" class="text-xs bg-green-600 text-white px-3 py-1 rounded-lg">+ Record Sale</a>
            </div>
            <table class="w-full text-sm">
                <thead class="text-xs text-gray-500 border-b">
                    <tr><th class="pb-2 text-left">Date</th><th class="pb-2">Qty</th><th class="pb-2 text-right">Unit Price</th><th class="pb-2 text-right">Revenue</th><th class="pb-2 text-right">Net Profit</th><th class="pb-2 text-left">Platform</th></tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($product->sales as $s)
                    <tr class="hover:bg-gray-50">
                        <td class="py-2">{{ $s->sale_date->format('d M Y') }}</td>
                        <td class="py-2 text-center">{{ $s->quantity }}</td>
                        <td class="py-2 text-right">৳{{ number_format($s->unit_price_bdt) }}</td>
                        <td class="py-2 text-right">৳{{ number_format($s->grossRevenue()) }}</td>
                        <td class="py-2 text-right font-semibold {{ $s->netProfit() >= 0 ? 'text-green-700' : 'text-red-600' }}">৳{{ number_format($s->netProfit()) }}</td>
                        <td class="py-2 pl-3 text-gray-500">{{ $s->platform ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="py-4 text-gray-400 text-center">No sales yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</x-app-layout>
