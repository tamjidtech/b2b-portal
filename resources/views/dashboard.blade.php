<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        {{-- Flash --}}
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded">{{ session('success') }}</div>
        @endif

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @php
                $cards = [
                    ['label'=>'Total Revenue',    'value'=>'৳'.number_format($totalRevenue),    'color'=>'bg-blue-50 border-blue-200'],
                    ['label'=>'Gross Profit',     'value'=>'৳'.number_format($grossProfit),     'color'=>$grossProfit>=0 ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'],
                    ['label'=>'Net Profit',       'value'=>'৳'.number_format($netProfit),       'color'=>$netProfit>=0 ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'],
                    ['label'=>'Inventory Value',  'value'=>'৳'.number_format($inventoryValue),  'color'=>'bg-yellow-50 border-yellow-200'],
                    ['label'=>'Active Products',  'value'=>$productCount,                        'color'=>'bg-indigo-50 border-indigo-200'],
                    ['label'=>'Completed Trips',  'value'=>$tripCount,                           'color'=>'bg-purple-50 border-purple-200'],
                    ['label'=>'Planned Trips',    'value'=>$pendingTrips,                        'color'=>'bg-orange-50 border-orange-200'],
                    ['label'=>'Trip Costs',       'value'=>'৳'.number_format($tripCosts),        'color'=>'bg-gray-50 border-gray-200'],
                ];
            @endphp
            @foreach($cards as $c)
            <div class="border rounded-xl p-4 {{ $c['color'] }}">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ $c['label'] }}</p>
                <p class="text-2xl font-bold text-gray-800 mt-1">{{ $c['value'] }}</p>
            </div>
            @endforeach
        </div>

        {{-- Top Products + Monthly Revenue --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Top Products --}}
            <div class="bg-white rounded-xl shadow p-5">
                <h3 class="font-semibold text-gray-700 mb-3">Top 5 Products by Net Profit</h3>
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-gray-500 border-b">
                        <th class="pb-2">Product</th>
                        <th class="pb-2 text-right">Net Profit</th>
                        <th class="pb-2 text-right">Stock</th>
                    </tr></thead>
                    <tbody>
                    @forelse($topProducts as $p)
                    <tr class="border-b last:border-0 hover:bg-gray-50">
                        <td class="py-2"><a href="{{ route('products.show', $p) }}" class="text-blue-600 hover:underline">{{ $p->name }}</a></td>
                        <td class="py-2 text-right font-medium {{ $p->totalNetProfit() >= 0 ? 'text-green-700' : 'text-red-600' }}">৳{{ number_format($p->totalNetProfit()) }}</td>
                        <td class="py-2 text-right text-gray-600">{{ $p->stockInHand() }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="py-4 text-gray-400 text-center">No data yet</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Monthly Revenue --}}
            <div class="bg-white rounded-xl shadow p-5">
                <h3 class="font-semibold text-gray-700 mb-3">Monthly Revenue (last 6 months)</h3>
                @if($monthlyRevenue->isEmpty())
                    <p class="text-gray-400 text-sm">No sales recorded yet.</p>
                @else
                    @php $max = $monthlyRevenue->max() ?: 1; @endphp
                    @foreach($monthlyRevenue as $month => $rev)
                    <div class="mb-2">
                        <div class="flex justify-between text-xs text-gray-600 mb-1">
                            <span>{{ $month }}</span><span>৳{{ number_format($rev) }}</span>
                        </div>
                        <div class="h-3 bg-gray-100 rounded">
                            <div class="h-3 bg-blue-500 rounded" style="width:{{ round($rev/$max*100) }}%"></div>
                        </div>
                    </div>
                    @endforeach
                @endif
            </div>
        </div>

        {{-- Recent Activity --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Recent Purchases --}}
            <div class="bg-white rounded-xl shadow p-5">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-semibold text-gray-700">Recent Purchases</h3>
                    <a href="{{ route('purchases.create') }}" class="text-xs bg-blue-600 text-white px-3 py-1 rounded-lg hover:bg-blue-700">+ Add</a>
                </div>
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-gray-500 border-b">
                        <th class="pb-2">Product</th><th class="pb-2">Qty</th><th class="pb-2 text-right">Cost</th>
                    </tr></thead>
                    <tbody>
                    @forelse($recentPurchases as $p)
                    <tr class="border-b last:border-0 hover:bg-gray-50">
                        <td class="py-2">{{ $p->product->name ?? '—' }}<br><span class="text-xs text-gray-400">{{ $p->purchase_date->format('d M Y') }}</span></td>
                        <td class="py-2">{{ $p->quantity }}</td>
                        <td class="py-2 text-right">৳{{ number_format($p->totalCostBdt()) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="py-4 text-gray-400 text-center">No purchases yet</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Recent Sales --}}
            <div class="bg-white rounded-xl shadow p-5">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-semibold text-gray-700">Recent Sales</h3>
                    <a href="{{ route('sales.create') }}" class="text-xs bg-green-600 text-white px-3 py-1 rounded-lg hover:bg-green-700">+ Add</a>
                </div>
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-gray-500 border-b">
                        <th class="pb-2">Product</th><th class="pb-2">Qty</th><th class="pb-2 text-right">Revenue</th>
                    </tr></thead>
                    <tbody>
                    @forelse($recentSales as $s)
                    <tr class="border-b last:border-0 hover:bg-gray-50">
                        <td class="py-2">{{ $s->product->name ?? '—' }}<br><span class="text-xs text-gray-400">{{ $s->sale_date->format('d M Y') }} · {{ $s->platform }}</span></td>
                        <td class="py-2">{{ $s->quantity }}</td>
                        <td class="py-2 text-right text-green-700 font-medium">৳{{ number_format($s->grossRevenue()) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="py-4 text-gray-400 text-center">No sales yet</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>
