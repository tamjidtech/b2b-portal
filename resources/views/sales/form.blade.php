<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">{{ $sale->exists ? 'Edit Sale' : 'Record Sale' }}</h2>
    </x-slot>
    <div class="py-8 max-w-2xl mx-auto px-4">
        <div class="bg-white shadow rounded-xl p-6">
            <form method="POST" action="{{ $sale->exists ? route('sales.update', $sale) : route('sales.store') }}">
                @csrf
                @if($sale->exists) @method('PATCH') @endif
                @if($errors->any())
                    <div class="mb-4 bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded text-sm">
                        <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Product *</label>
                        <select name="product_id" class="w-full border rounded px-3 py-2" required>
                            <option value="">-- Select Product --</option>
                            @foreach($products as $p)
                            <option value="{{ $p->id }}" @selected(old('product_id', $sale->product_id ?? request('product_id'))==$p->id)>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Linked Purchase (optional — for accurate profit)</label>
                        <select name="purchase_id" class="w-full border rounded px-3 py-2">
                            <option value="">-- None (use estimated cost) --</option>
                            @foreach($purchases as $pu)
                            <option value="{{ $pu->id }}" @selected(old('purchase_id', $sale->purchase_id)==$pu->id)>
                                {{ $pu->product->name ?? '?' }} — {{ $pu->purchase_date->format('d M Y') }} — {{ $pu->unitsRemaining() }} remaining
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sale Date *</label>
                        <input name="sale_date" type="date" value="{{ old('sale_date', $sale->sale_date?->format('Y-m-d') ?? date('Y-m-d')) }}" class="w-full border rounded px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantity *</label>
                        <input name="quantity" type="number" min="1" value="{{ old('quantity', $sale->quantity ?? 1) }}" class="w-full border rounded px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit Selling Price (BDT) *</label>
                        <input name="unit_price_bdt" type="number" step="0.01" value="{{ old('unit_price_bdt', $sale->unit_price_bdt) }}" class="w-full border rounded px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Platform</label>
                        <input name="platform" list="platforms" value="{{ old('platform', $sale->platform) }}" placeholder="FB, Daraz, Direct…" class="w-full border rounded px-3 py-2">
                        <datalist id="platforms">
                            <option value="Facebook">
                            <option value="Daraz">
                            <option value="Direct">
                            <option value="WhatsApp">
                            <option value="Amazon.sa">
                        </datalist>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Platform Fee (BDT)</label>
                        <input name="platform_fee_bdt" type="number" step="0.01" value="{{ old('platform_fee_bdt', $sale->platform_fee_bdt ?? 0) }}" class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Cost (BDT)</label>
                        <input name="delivery_cost_bdt" type="number" step="0.01" value="{{ old('delivery_cost_bdt', $sale->delivery_cost_bdt ?? 0) }}" class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Buyer Ref</label>
                        <input name="buyer_ref" value="{{ old('buyer_ref', $sale->buyer_ref) }}" class="w-full border rounded px-3 py-2">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea name="notes" rows="2" class="w-full border rounded px-3 py-2">{{ old('notes', $sale->notes) }}</textarea>
                    </div>
                </div>
                <div class="mt-5 flex gap-3">
                    <button type="submit" class="bg-green-600 text-white px-5 py-2 rounded-lg hover:bg-green-700">{{ $sale->exists ? 'Update' : 'Record Sale' }}</button>
                    <a href="{{ route('sales.index') }}" class="px-5 py-2 border rounded-lg text-gray-600 hover:bg-gray-50">Cancel</a>
                    @if($sale->exists)
                    <form method="POST" action="{{ route('sales.destroy', $sale) }}" class="ml-auto" onsubmit="return confirm('Delete this sale?')">
                        @csrf @method('DELETE')
                        <button class="text-red-600 hover:underline text-sm">Delete</button>
                    </form>
                    @endif
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
