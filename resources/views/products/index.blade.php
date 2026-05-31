<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Products</h2>
            <a href="{{ route('products.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">+ Add Product</a>
        </div>
    </x-slot>
    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded">{{ session('success') }}</div>
        @endif
        {{-- Filters --}}
        <form method="GET" class="flex gap-3 mb-5">
            <select name="pipeline" class="border rounded px-3 py-2 text-sm">
                <option value="">All Pipelines</option>
                <option value="SA_TO_BD" @selected(request('pipeline')=='SA_TO_BD')>SA → BD</option>
                <option value="BD_TO_SA" @selected(request('pipeline')=='BD_TO_SA')>BD → SA</option>
            </select>
            <input name="search" value="{{ request('search') }}" placeholder="Search name/category…" class="border rounded px-3 py-2 text-sm flex-1">
            <button class="bg-gray-700 text-white px-4 py-2 rounded text-sm">Filter</button>
            <a href="{{ route('products.index') }}" class="px-4 py-2 text-sm text-gray-600 border rounded hover:bg-gray-50">Clear</a>
        </form>

        <div class="bg-white shadow rounded-xl overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Product</th>
                        <th class="px-4 py-3">Pipeline</th>
                        <th class="px-4 py-3">Rating</th>
                        <th class="px-4 py-3">Risk</th>
                        <th class="px-4 py-3 text-right">Buy (est.)</th>
                        <th class="px-4 py-3 text-right">Sell (est.)</th>
                        <th class="px-4 py-3 text-right">Est. Profit</th>
                        <th class="px-4 py-3 text-right">Stock</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($products as $p)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if ($p->imageSrc())
                                    <img src="{{ $p->imageSrc() }}" alt="{{ $p->name }}"
                                        class="w-10 h-10 object-contain rounded border border-gray-100 bg-gray-50 p-0.5 shrink-0"
                                        onerror="this.style.display='none'">
                                @else
                                    <div class="w-10 h-10 rounded border border-gray-100 bg-gray-100 flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                @endif
                                <div>
                                    <a href="{{ route('products.show', $p) }}" class="font-medium text-blue-600 hover:underline">{{ $p->name }}</a>
                                    <p class="text-xs text-gray-400">{{ $p->category }} · {{ $p->source_market }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $p->pipeline === 'SA_TO_BD' ? 'bg-orange-100 text-orange-700' : 'bg-green-100 text-green-700' }}">
                                {{ $p->pipeline === 'SA_TO_BD' ? 'SA→BD' : 'BD→SA' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-yellow-500">{{ str_repeat('★', (int)$p->rating) }}{{ $p->rating - (int)$p->rating >= 0.5 ? '½' : '' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded text-xs font-bold {{ $p->risk === 'L' ? 'bg-green-100 text-green-700' : ($p->risk === 'M' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">{{ $p->risk }}</span>
                        </td>
                        <td class="px-4 py-3 text-right text-gray-600">৳{{ number_format($p->estimated_buy_price_bdt) }}</td>
                        <td class="px-4 py-3 text-right text-gray-600">৳{{ number_format($p->estimated_sell_price_bdt) }}</td>
                        <td class="px-4 py-3 text-right font-semibold {{ $p->estimatedProfitPerUnit() >= 0 ? 'text-green-700' : 'text-red-600' }}">৳{{ number_format($p->estimatedProfitPerUnit()) }}</td>
                        <td class="px-4 py-3 text-right text-gray-700 font-medium">{{ $p->stockInHand() }}</td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <a href="{{ route('products.edit', $p) }}" class="text-indigo-600 hover:underline text-xs">Edit</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="px-4 py-8 text-center text-gray-400">No products found. <a href="{{ route('products.create') }}" class="text-blue-600 underline">Add one</a>.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $products->links() }}</div>
    </div>
</x-app-layout>
