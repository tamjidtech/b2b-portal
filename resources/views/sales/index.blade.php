<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">Sales</h2>
            <a href="{{ route('sales.create') }}" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700">+ Record Sale</a>
        </div>
    </x-slot>
    <div class="py-8 max-w-7xl mx-auto px-4">
        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded">{{ session('success') }}</div>
        @endif
        <form method="GET" class="flex gap-3 mb-5">
            <select name="product_id" class="border rounded px-3 py-2 text-sm">
                <option value="">All Products</option>
                @foreach($products as $p)
                <option value="{{ $p->id }}" @selected(request('product_id')==$p->id)>{{ $p->name }}</option>
                @endforeach
            </select>
            <button class="bg-gray-700 text-white px-4 py-2 rounded text-sm">Filter</button>
            <a href="{{ route('sales.index') }}" class="px-4 py-2 text-sm text-gray-600 border rounded hover:bg-gray-50">Clear</a>
        </form>
        <div class="bg-white shadow rounded-xl overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Product</th>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Platform</th>
                        <th class="px-4 py-3 text-right">Qty</th>
                        <th class="px-4 py-3 text-right">Unit Price</th>
                        <th class="px-4 py-3 text-right">Gross Revenue</th>
                        <th class="px-4 py-3 text-right">Platform Fee</th>
                        <th class="px-4 py-3 text-right">Delivery</th>
                        <th class="px-4 py-3 text-right">Net Profit</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($sales as $s)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium">
                            <a href="{{ route('products.show', $s->product_id) }}" class="text-blue-600 hover:underline">{{ $s->product->name ?? '—' }}</a>
                        </td>
                        <td class="px-4 py-3 text-center">{{ $s->sale_date->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-center text-gray-500">{{ $s->platform ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">{{ $s->quantity }}</td>
                        <td class="px-4 py-3 text-right">৳{{ number_format($s->unit_price_bdt) }}</td>
                        <td class="px-4 py-3 text-right">৳{{ number_format($s->grossRevenue()) }}</td>
                        <td class="px-4 py-3 text-right text-gray-500">৳{{ number_format($s->platform_fee_bdt) }}</td>
                        <td class="px-4 py-3 text-right text-gray-500">৳{{ number_format($s->delivery_cost_bdt) }}</td>
                        <td class="px-4 py-3 text-right font-semibold {{ $s->netProfit() >= 0 ? 'text-green-700' : 'text-red-600' }}">৳{{ number_format($s->netProfit()) }}</td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <a href="{{ route('sales.show', $s) }}" class="text-blue-600 hover:underline text-xs">View</a>
                            <a href="{{ route('sales.edit', $s) }}" class="text-indigo-600 hover:underline text-xs">Edit</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="px-4 py-8 text-center text-gray-400">No sales yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $sales->links() }}</div>
    </div>
</x-app-layout>
