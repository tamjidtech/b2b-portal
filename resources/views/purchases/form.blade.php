<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">{{ $purchase->exists ? 'Edit Purchase' : 'Record Purchase' }}</h2>
    </x-slot>
    <div class="py-8 max-w-2xl mx-auto px-4">
        <div class="bg-white shadow rounded-xl p-6">
            <form method="POST" action="{{ $purchase->exists ? route('purchases.update', $purchase) : route('purchases.store') }}">
                @csrf
                @if($purchase->exists) @method('PATCH') @endif
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
                            <option value="{{ $p->id }}" @selected(old('product_id', $purchase->product_id)==$p->id)>{{ $p->name }} ({{ $p->pipeline === 'SA_TO_BD' ? 'SA→BD' : 'BD→SA' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Trip (optional)</label>
                        <select name="trip_id" class="w-full border rounded px-3 py-2">
                            <option value="">-- No Trip --</option>
                            @foreach($trips as $t)
                            <option value="{{ $t->id }}" @selected(old('trip_id', $purchase->trip_id)==$t->id)>{{ $t->name }} ({{ $t->trip_date->format('d M Y') }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Purchase Date *</label>
                        <input name="purchase_date" type="date" value="{{ old('purchase_date', $purchase->purchase_date?->format('Y-m-d') ?? date('Y-m-d')) }}" class="w-full border rounded px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantity *</label>
                        <input name="quantity" type="number" min="1" value="{{ old('quantity', $purchase->quantity ?? 1) }}" class="w-full border rounded px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit Cost (BDT) *</label>
                        <input name="unit_cost_bdt" type="number" step="0.01" value="{{ old('unit_cost_bdt', $purchase->unit_cost_bdt) }}" class="w-full border rounded px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Shipping Cost (BDT)</label>
                        <input name="shipping_cost_bdt" type="number" step="0.01" value="{{ old('shipping_cost_bdt', $purchase->shipping_cost_bdt ?? 0) }}" class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Customs / Duty (BDT)</label>
                        <input name="customs_cost_bdt" type="number" step="0.01" value="{{ old('customs_cost_bdt', $purchase->customs_cost_bdt ?? 0) }}" class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Other Cost (BDT)</label>
                        <input name="other_cost_bdt" type="number" step="0.01" value="{{ old('other_cost_bdt', $purchase->other_cost_bdt ?? 0) }}" class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Invoice / Ref</label>
                        <input name="invoice_ref" value="{{ old('invoice_ref', $purchase->invoice_ref) }}" class="w-full border rounded px-3 py-2">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea name="notes" rows="2" class="w-full border rounded px-3 py-2">{{ old('notes', $purchase->notes) }}</textarea>
                    </div>
                </div>
                <div class="mt-5 flex gap-3">
                    <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700">{{ $purchase->exists ? 'Update' : 'Record' }}</button>
                    <a href="{{ route('purchases.index') }}" class="px-5 py-2 border rounded-lg text-gray-600 hover:bg-gray-50">Cancel</a>
                    @if($purchase->exists)
                    <form method="POST" action="{{ route('purchases.destroy', $purchase) }}" class="ml-auto" onsubmit="return confirm('Delete this purchase?')">
                        @csrf @method('DELETE')
                        <button class="text-red-600 hover:underline text-sm">Delete</button>
                    </form>
                    @endif
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
