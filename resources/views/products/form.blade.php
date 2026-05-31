<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $product->exists ? 'Edit Product' : 'Add Product' }}
        </h2>
    </x-slot>
    <div class="py-8 max-w-3xl mx-auto px-4">
        <div class="bg-white shadow rounded-xl p-6">
            <form method="POST"
                  action="{{ $product->exists ? route('products.update', $product) : route('products.store') }}"
                  enctype="multipart/form-data">
                @csrf
                @if($product->exists) @method('PATCH') @endif
                @if($errors->any())
                    <div class="mb-4 bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded text-sm">
                        <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
                        <input name="name" value="{{ old('name', $product->name) }}" class="w-full border rounded px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                        <input name="category" value="{{ old('category', $product->category) }}" placeholder="Skincare, Electronics…" class="w-full border rounded px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pipeline *</label>
                        <select name="pipeline" class="w-full border rounded px-3 py-2" required>
                            <option value="SA_TO_BD" @selected(old('pipeline', $product->pipeline)=='SA_TO_BD')>SA → BD (Buy in Saudi, Sell in BD)</option>
                            <option value="BD_TO_SA" @selected(old('pipeline', $product->pipeline)=='BD_TO_SA')>BD → SA (Buy in BD, Sell in Saudi)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Source Market *</label>
                        <input name="source_market" value="{{ old('source_market', $product->source_market) }}" placeholder="Amazon.sa, Daraz BD…" class="w-full border rounded px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Product URL</label>
                        <input name="url" type="url" value="{{ old('url', $product->url) }}" placeholder="https://..." class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Est. Buy Price (BDT) *</label>
                        <input name="estimated_buy_price_bdt" type="number" step="0.01" value="{{ old('estimated_buy_price_bdt', $product->estimated_buy_price_bdt) }}" class="w-full border rounded px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Est. Sell Price (BDT) *</label>
                        <input name="estimated_sell_price_bdt" type="number" step="0.01" value="{{ old('estimated_sell_price_bdt', $product->estimated_sell_price_bdt) }}" class="w-full border rounded px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Rating (1-5)</label>
                        <input name="rating" type="number" step="0.5" min="0" max="5" value="{{ old('rating', $product->rating ?? 3) }}" class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Risk Level *</label>
                        <select name="risk" class="w-full border rounded px-3 py-2" required>
                            <option value="L" @selected(old('risk', $product->risk)=='L')>Low</option>
                            <option value="M" @selected(old('risk', $product->risk)=='M')>Medium</option>
                            <option value="H" @selected(old('risk', $product->risk)=='H')>High</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                        <select name="status" class="w-full border rounded px-3 py-2" required>
                            <option value="research" @selected(old('status', $product->status)=='research')>Research</option>
                            <option value="active" @selected(old('status', $product->status)=='active')>Active</option>
                            <option value="paused" @selected(old('status', $product->status)=='paused')>Paused</option>
                            <option value="discontinued" @selected(old('status', $product->status)=='discontinued')>Discontinued</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Weight (grams)</label>
                        <input name="weight_grams" type="number" min="0" value="{{ old('weight_grams', $product->weight_grams) }}" class="w-full border rounded px-3 py-2">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes / Review</label>
                        <textarea name="notes" rows="3" class="w-full border rounded px-3 py-2">{{ old('notes', $product->notes) }}</textarea>
                    </div>

                    {{-- Image --}}
                    <div class="md:col-span-2" x-data="{
                        preview: '{{ $product->imageSrc() ?? '' }}',
                        tab: 'upload',
                        removeImg: false,
                        setUrl(val) { this.preview = val || ''; },
                        setFile(e) {
                            const f = e.target.files[0];
                            if (f) { const r = new FileReader(); r.onload = ev => this.preview = ev.target.result; r.readAsDataURL(f); }
                        }
                    }">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Product Image</label>

                        {{-- Tabs --}}
                        <div class="flex gap-2 mb-3">
                            <button type="button" @click="tab='upload'"
                                :class="tab==='upload' ? 'bg-gray-800 text-white' : 'border border-gray-300 text-gray-600 hover:bg-gray-50'"
                                class="px-3 py-1 rounded text-xs font-medium transition-colors">Upload File</button>
                            <button type="button" @click="tab='url'"
                                :class="tab==='url' ? 'bg-gray-800 text-white' : 'border border-gray-300 text-gray-600 hover:bg-gray-50'"
                                class="px-3 py-1 rounded text-xs font-medium transition-colors">Paste Image URL</button>
                        </div>

                        <div x-show="tab==='upload'">
                            <input type="file" name="image_file" accept="image/*" @change="setFile($event)"
                                class="w-full text-sm border border-gray-300 rounded px-3 py-2 file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-sm file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>
                        <div x-show="tab==='url'" class="flex gap-2">
                            <input type="url" name="image_url" value="{{ old('image_url') }}"
                                placeholder="https://m.media-amazon.com/images/…"
                                @input="setUrl($event.target.value)"
                                class="flex-1 border border-gray-300 rounded px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        {{-- Preview --}}
                        <div x-show="preview && !removeImg" class="mt-3 flex items-start gap-4">
                            <img :src="preview" alt="preview" class="w-28 h-28 object-contain rounded-lg border border-gray-200 bg-gray-50 p-1">
                            @if ($product->exists && $product->image)
                                <div class="flex flex-col gap-2">
                                    <p class="text-xs text-gray-500">Current image</p>
                                    <label class="flex items-center gap-2 text-xs text-red-600 cursor-pointer">
                                        <input type="checkbox" name="remove_image" value="1" x-model="removeImg"> Remove image
                                    </label>
                                </div>
                            @endif
                        </div>
                        <div x-show="removeImg" class="mt-2 text-xs text-red-500">Image will be removed on save.</div>
                    </div>

                </div>{{-- end grid --}}

                <div class="mt-5 flex gap-3">
                    <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700">
                        {{ $product->exists ? 'Update' : 'Add Product' }}
                    </button>
                    <a href="{{ route('products.index') }}" class="px-5 py-2 border rounded-lg text-gray-600 hover:bg-gray-50">Cancel</a>
                    @if($product->exists)
                    <form method="POST" action="{{ route('products.destroy', $product) }}" class="ml-auto" onsubmit="return confirm('Delete this product?')">
                        @csrf @method('DELETE')
                        <button class="text-red-600 hover:underline text-sm">Delete</button>
                    </form>
                    @endif
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
