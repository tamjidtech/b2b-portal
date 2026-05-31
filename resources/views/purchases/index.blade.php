<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">Purchases</h2>
            <a href="{{ route('purchases.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">+ Record Purchase</a>
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
            <a href="{{ route('purchases.index') }}" class="px-4 py-2 text-sm text-gray-600 border rounded hover:bg-gray-50">Clear</a>
        </form>
        <div class="bg-white shadow rounded-xl overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Product</th>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Trip</th>
                        <th class="px-4 py-3 text-right">Qty</th>
                        <th class="px-4 py-3 text-right">Unit Cost</th>
                        <th class="px-4 py-3 text-right">Shipping</th>
                        <th class="px-4 py-3 text-right">Customs</th>
                        <th class="px-4 py-3 text-right">Total Cost</th>
                        <th class="px-4 py-3 text-right">All-in/unit</th>
                        <th class="px-4 py-3 text-right">Remaining</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($purchases as $pu)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium">
                            <a href="{{ route('products.show', $pu->product_id) }}" class="text-blue-600 hover:underline">{{ $pu->product->name ?? '—' }}</a>
                        </td>
                        <td class="px-4 py-3 text-center">{{ $pu->purchase_date->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-center text-gray-500">{{ $pu->trip->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">{{ $pu->quantity }}</td>
                        <td class="px-4 py-3 text-right">৳{{ number_format($pu->unit_cost_bdt) }}</td>
                        <td class="px-4 py-3 text-right">৳{{ number_format($pu->shipping_cost_bdt) }}</td>
                        <td class="px-4 py-3 text-right">৳{{ number_format($pu->customs_cost_bdt) }}</td>
                        <td class="px-4 py-3 text-right font-semibold">৳{{ number_format($pu->totalCostBdt()) }}</td>
                        <td class="px-4 py-3 text-right text-blue-700 font-medium">৳{{ number_format($pu->allInUnitCost()) }}</td>
                        <td class="px-4 py-3 text-right {{ $pu->unitsRemaining() == 0 ? 'text-red-500' : 'text-green-700' }} font-medium">{{ $pu->unitsRemaining() }}</td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <a href="{{ route('purchases.show', $pu) }}" class="text-blue-600 hover:underline text-xs">View</a>
                            <a href="{{ route('purchases.edit', $pu) }}" class="text-indigo-600 hover:underline text-xs">Edit</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="11" class="px-4 py-8 text-center text-gray-400">No purchases yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $purchases->links() }}</div>
    </div>
</x-app-layout>
