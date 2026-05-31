<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Product Research Board</h2>
            <span class="text-sm text-gray-500">{{ $stats['total'] }} products tracked</span>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6"
             x-data="{
                addOpen: {{ session('open_add_form') || $errors->any() ? 'true' : 'false' }},
                pipeline: '{{ old('pipeline', 'SA_TO_BD') }}',
                productName: '{{ old('name', '') }}',
                amazonSearch() {
                    if (this.productName.trim()) {
                        const q = encodeURIComponent(this.productName.trim());
                        const url = this.pipeline === 'BD_TO_SA'
                            ? 'https://www.daraz.com.bd/catalog/?q=' + q
                            : 'https://www.amazon.sa/-/en/s?k=' + q;
                        window.open(url, '_blank');
                    }
                }
             }"
             @quick-import-use.window="
                addOpen = true;
                if ($event.detail.pipeline) pipeline = $event.detail.pipeline;
                if ($event.detail.name)     productName = $event.detail.name;
             ">

            {{-- ======================================================
                 FLASH / ERROR MESSAGES
            ====================================================== --}}
            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
                    ✓ {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
                    ✗ {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
                    <ul class="space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ======================================================
                 STATS BAR
            ====================================================== --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div class="bg-white rounded-lg border border-gray-100 p-3">
                    <p class="text-xs text-gray-500">Total Tracked</p>
                    <p class="text-2xl font-bold text-gray-900 mt-0.5">{{ $stats['total'] }}</p>
                </div>
                <div class="bg-white rounded-lg border border-gray-100 p-3">
                    <p class="text-xs text-gray-500">With Image</p>
                    <p class="text-2xl font-bold text-indigo-600 mt-0.5">{{ $stats['with_image'] }}<span class="text-xs text-gray-400 font-normal">/{{ $stats['total'] }}</span></p>
                </div>
                <div class="bg-white rounded-lg border border-gray-100 p-3">
                    <p class="text-xs text-gray-500">High Rated (4★+)</p>
                    <p class="text-2xl font-bold text-yellow-500 mt-0.5">{{ $stats['high_rated'] }}</p>
                </div>
                <div class="bg-white rounded-lg border border-gray-100 p-3">
                    <p class="text-xs text-gray-500">Low Risk</p>
                    <p class="text-2xl font-bold text-green-600 mt-0.5">{{ $stats['low_risk'] }}</p>
                </div>
            </div>

            {{-- ======================================================
                 QUICK IMPORT FROM URL
            ====================================================== --}}
            <div class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-xl border border-indigo-200 p-5"
                 x-data="quickImport()">
                <div class="flex items-start gap-4">
                    <div class="bg-indigo-600 text-white rounded-lg p-2 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-bold text-gray-900 text-base">Quick Import from URL</h3>
                        <p class="text-xs text-gray-600 mt-0.5">Paste an Amazon.sa, Daraz, or Noon product URL — we'll auto-extract the name, image, and price.</p>

                        <div class="flex gap-2 mt-3">
                            <input type="url" x-model="url"
                                @keydown.enter.prevent="preview()"
                                placeholder="https://www.amazon.sa/-/en/dp/..."
                                class="flex-1 border-indigo-200 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <button type="button" @click="preview()" :disabled="loading"
                                class="bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-300 text-white px-5 py-2 rounded-md text-sm font-semibold whitespace-nowrap flex items-center gap-1.5">
                                <svg x-show="!loading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                                <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                <span x-text="loading ? 'Fetching…' : 'Import'"></span>
                            </button>
                        </div>

                        {{-- Preview result --}}
                        <div x-show="result" x-cloak class="mt-4 bg-white rounded-lg border border-gray-200 p-4">
                            <div x-show="result && result.error" class="text-red-700 text-sm bg-red-50 border border-red-200 rounded p-3" x-text="result?.error"></div>

                            <div x-show="result && !result.error" class="space-y-3">
                                <div class="flex gap-4">
                                    <div class="w-28 h-28 shrink-0 rounded-lg overflow-hidden border border-gray-200 bg-gray-50 flex items-center justify-center">
                                        <img x-show="result?.image" :src="result?.image" class="max-h-full max-w-full object-contain p-1">
                                        <span x-show="!result?.image" class="text-gray-300 text-xs">No image</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-gray-900 text-sm leading-snug" x-text="result?.name || '(no name extracted)'"></p>
                                        <div class="flex flex-wrap gap-2 mt-2 text-xs">
                                            <span x-show="result?.source" class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded-full" x-text="'Source: ' + result?.source"></span>
                                            <span x-show="result?.pipeline" class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full"
                                                x-text="result?.pipeline === 'SA_TO_BD' ? 'SA → BD' : 'BD → SA'"></span>
                                            <span x-show="result?.category" class="bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full" x-text="result?.category"></span>
                                        </div>
                                        <div x-show="result?.price_local" class="mt-2 text-sm">
                                            <span class="text-gray-500">Price:</span>
                                            <span class="font-semibold text-gray-800" x-text="result?.price_local + ' ' + (result?.currency || '')"></span>
                                            <span x-show="result?.price_bdt && result?.currency !== 'BDT'" class="text-gray-400 text-xs">
                                                (≈ ৳<span x-text="Number(result?.price_bdt).toLocaleString()"></span>)
                                            </span>
                                        </div>
                                        <div x-show="result?.duplicate" class="mt-2 text-xs bg-amber-50 border border-amber-200 text-amber-800 rounded p-2">
                                            ⚠ Looks like a duplicate of <strong x-text="result?.duplicate?.name"></strong>
                                            (<span x-text="result?.duplicate?.status"></span>)
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-2 pt-2 border-t border-gray-100">
                                    <button type="button" @click="useThisData()"
                                        :disabled="result?.duplicate"
                                        class="bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-300 text-white text-xs font-semibold px-4 py-2 rounded-md">
                                        ✓ Use this data → Pre-fill form
                                    </button>
                                    <button type="button" @click="clear()" class="text-gray-500 hover:text-gray-700 text-xs px-3">Clear</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ======================================================
                 ADD NEW PRODUCT PANEL
            ====================================================== --}}
            <div class="bg-white rounded-xl border border-indigo-100 shadow-sm overflow-hidden">
                <button @click="addOpen = !addOpen"
                    class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-indigo-50 transition-colors">
                    <div class="flex items-center gap-3">
                        <span class="bg-indigo-600 text-white rounded-lg p-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </span>
                        <span class="font-semibold text-gray-800">Add New Product to Research</span>
                        <span class="text-xs text-gray-400">Research a new product and track it here</span>
                    </div>
                    <svg :class="addOpen ? 'rotate-180' : ''" class="w-5 h-5 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="addOpen" x-cloak x-transition class="border-t border-gray-100">
                    <form method="POST" action="{{ route('research.store') }}" enctype="multipart/form-data" class="p-6">
                        @csrf

                        {{-- Pipeline selector (prominent, drives search URL) --}}
                        <div class="mb-5">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Trade Direction *</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="pipeline" value="SA_TO_BD" x-model="pipeline" class="peer sr-only">
                                    <div class="border-2 rounded-lg p-3 text-center peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:bg-gray-50 transition-colors">
                                        <p class="font-semibold text-sm text-blue-700">Saudi Arabia → Bangladesh</p>
                                        <p class="text-xs text-gray-500 mt-0.5">Buy from Amazon.sa, sell in BD</p>
                                    </div>
                                </label>
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="pipeline" value="BD_TO_SA" x-model="pipeline" class="peer sr-only">
                                    <div class="border-2 rounded-lg p-3 text-center peer-checked:border-purple-500 peer-checked:bg-purple-50 hover:bg-gray-50 transition-colors">
                                        <p class="font-semibold text-sm text-purple-700">Bangladesh → Saudi Arabia</p>
                                        <p class="text-xs text-gray-500 mt-0.5">Source from BD, sell in SA</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- Name + Search button --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
                            <div class="flex gap-2">
                                <input type="text" name="name" x-model="productName"
                                    value="{{ old('name') }}"
                                    placeholder="e.g. L'Oreal Paris Serum 30ml"
                                    class="flex-1 border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-400 @enderror">
                                <button type="button" @click="amazonSearch()"
                                    class="shrink-0 px-4 py-2 bg-yellow-400 hover:bg-yellow-500 text-gray-900 text-sm font-medium rounded-md flex items-center gap-1.5 transition-colors"
                                    title="Search this product on Amazon.sa or Daraz.com.bd">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                    Search Online ↗
                                </button>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">
                                Type a name, click "Search Online" to look it up on
                                <span x-text="pipeline === 'BD_TO_SA' ? 'Daraz.com.bd' : 'Amazon.sa'"></span>, then fill in the prices below.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                            {{-- Category --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                                <input type="text" name="category" value="{{ old('category') }}"
                                    list="category-suggestions"
                                    placeholder="e.g. Skincare, Electronics…"
                                    class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <datalist id="category-suggestions">
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat }}">
                                    @endforeach
                                    <option value="Skincare"><option value="Personal Care"><option value="Electronics">
                                    <option value="Fragrance"><option value="Supplements"><option value="Toys">
                                    <option value="Fitness"><option value="Kitchen"><option value="Fashion">
                                    <option value="Grocery"><option value="Beverages"><option value="Snacks">
                                    <option value="Apparel"><option value="Home Decor"><option value="Baby">
                                </datalist>
                            </div>

                            {{-- Source Market --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Source Market *</label>
                                <input type="text" name="source_market"
                                    value="{{ old('source_market') }}"
                                    :placeholder="pipeline === 'BD_TO_SA' ? 'e.g. Daraz BD, Chawk Bazar…' : 'Amazon.sa'"
                                    class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>

                            {{-- Product URL --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Product URL</label>
                                <input type="url" name="url" value="{{ old('url') }}"
                                    placeholder="https://www.amazon.sa/…"
                                    class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>

                            {{-- Buy Price --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Buy Price (BDT) *
                                    <span class="text-gray-400 font-normal" x-text="pipeline === 'BD_TO_SA' ? '— what you pay in BD' : '— converted from SAR'"></span>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-gray-500 text-sm">৳</span>
                                    <input type="number" name="estimated_buy_price_bdt" value="{{ old('estimated_buy_price_bdt') }}"
                                        step="0.01" min="0" placeholder="0"
                                        class="w-full pl-7 border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                            </div>

                            {{-- Sell Price --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Sell Price (BDT) *
                                    <span class="text-gray-400 font-normal" x-text="pipeline === 'BD_TO_SA' ? '— converted from SAR' : '— what you sell for in BD'"></span>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-gray-500 text-sm">৳</span>
                                    <input type="number" name="estimated_sell_price_bdt" value="{{ old('estimated_sell_price_bdt') }}"
                                        step="0.01" min="0" placeholder="0"
                                        class="w-full pl-7 border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                            </div>

                            {{-- Weight --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Weight (grams)</label>
                                <input type="number" name="weight_grams" value="{{ old('weight_grams', 0) }}"
                                    min="0" placeholder="0"
                                    class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>

                            {{-- Rating --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Opportunity Rating (0–5)</label>
                                <input type="number" name="rating" value="{{ old('rating', 3.5) }}"
                                    step="0.1" min="0" max="5" placeholder="3.5"
                                    class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>

                            {{-- Risk --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Risk Level *</label>
                                <select name="risk" class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="L" @selected(old('risk')=='L')>Low</option>
                                    <option value="M" @selected(old('risk','M')=='M')>Medium</option>
                                    <option value="H" @selected(old('risk')=='H')>High</option>
                                </select>
                            </div>
                        </div>

                        {{-- Notes --}}
                        <div class="mb-5" x-data="{
                            preview: '',
                            imgTab: 'upload',
                            setFile(e) {
                                const f = e.target.files[0];
                                if (f) { const r = new FileReader(); r.onload = ev => this.preview = ev.target.result; r.readAsDataURL(f); }
                            },
                            setUrl(v) { this.preview = v || ''; }
                        }">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Product Image <span class="text-gray-400 font-normal">(optional)</span></label>
                            <div class="flex gap-2 mb-2">
                                <button type="button" @click="imgTab='upload'"
                                    :class="imgTab==='upload' ? 'bg-gray-700 text-white' : 'border border-gray-300 text-gray-500 hover:bg-gray-50'"
                                    class="px-3 py-1 rounded text-xs font-medium transition-colors">Upload</button>
                                <button type="button" @click="imgTab='url'"
                                    :class="imgTab==='url' ? 'bg-gray-700 text-white' : 'border border-gray-300 text-gray-500 hover:bg-gray-50'"
                                    class="px-3 py-1 rounded text-xs font-medium transition-colors">Paste URL</button>
                                <span class="text-xs text-gray-400 self-center ml-1">Copy image URL from Amazon / Daraz</span>
                            </div>
                            <div x-show="imgTab==='upload'">
                                <input type="file" name="image_file" accept="image/*" @change="setFile($event)"
                                    class="w-full text-sm border border-gray-300 rounded px-3 py-2 file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:bg-indigo-50 file:text-indigo-700">
                            </div>
                            <div x-show="imgTab==='url'">
                                <input type="url" name="image_url" value="{{ old('image_url') }}"
                                    placeholder="https://m.media-amazon.com/images/…"
                                    @input="setUrl($event.target.value)"
                                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div x-show="preview" class="mt-2">
                                <img :src="preview" class="w-24 h-24 object-contain rounded-lg border border-gray-200 bg-gray-50 p-1">
                            </div>
                        </div>

                        <div class="mb-5">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Research Notes</label>
                            <textarea name="notes" rows="2"
                                placeholder="Why is this a good opportunity? Competition, demand, margin notes…"
                                class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('notes') }}</textarea>
                        </div>

                        <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-md text-sm font-semibold transition-colors">
                                Save to Research Board
                            </button>
                            <button type="button" @click="addOpen = false" class="text-gray-500 hover:text-gray-700 text-sm">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ======================================================
                 FILTER BAR
            ====================================================== --}}
            <form method="GET" action="{{ route('research.index') }}" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search product name…"
                        class="col-span-2 md:col-span-1 border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">

                    <select name="pipeline" class="border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Pipelines</option>
                        <option value="SA_TO_BD" @selected(request('pipeline') === 'SA_TO_BD')>SA → BD</option>
                        <option value="BD_TO_SA" @selected(request('pipeline') === 'BD_TO_SA')>BD → SA</option>
                    </select>

                    <select name="category" class="border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Categories</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat }}" @selected(request('category') === $cat)>{{ $cat }}</option>
                        @endforeach
                    </select>

                    <select name="risk" class="border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Risk</option>
                        <option value="L" @selected(request('risk') === 'L')>Low Risk</option>
                        <option value="M" @selected(request('risk') === 'M')>Medium Risk</option>
                        <option value="H" @selected(request('risk') === 'H')>High Risk</option>
                    </select>

                    <select name="min_rating" class="border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Min Rating</option>
                        <option value="4.5" @selected(request('min_rating') == '4.5')>★ 4.5+</option>
                        <option value="4.0" @selected(request('min_rating') == '4.0')>★ 4.0+</option>
                        <option value="3.5" @selected(request('min_rating') == '3.5')>★ 3.5+</option>
                    </select>
                </div>
                <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-3">
                    <select name="sort" class="border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="rating_desc"  @selected($sort==='rating_desc')>★ Sort: Highest Rating</option>
                        <option value="rating_asc"   @selected($sort==='rating_asc')>★ Sort: Lowest Rating</option>
                        <option value="profit_desc"  @selected($sort==='profit_desc')>৳ Sort: Highest Profit</option>
                        <option value="profit_asc"   @selected($sort==='profit_asc')>৳ Sort: Lowest Profit</option>
                        <option value="margin_desc"  @selected($sort==='margin_desc')>% Sort: Highest Margin</option>
                        <option value="newest"       @selected($sort==='newest')>↓ Sort: Newest First</option>
                        <option value="oldest"       @selected($sort==='oldest')>↑ Sort: Oldest First</option>
                        <option value="name_asc"     @selected($sort==='name_asc')>A–Z Sort: Name</option>
                    </select>
                    <label class="flex items-center gap-2 text-sm text-gray-600 px-3 py-1.5 border border-gray-200 rounded-md bg-gray-50">
                        <input type="checkbox" name="no_image" value="1" @checked(request('no_image'))
                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        Only products without images
                    </label>
                    <label class="flex items-center gap-2 text-sm text-gray-600 px-3 py-1.5 border border-gray-200 rounded-md bg-gray-50">
                        <input type="checkbox" name="has_image" value="1" @checked(request('has_image'))
                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        Only products with images
                    </label>
                </div>
                <div class="mt-3 flex flex-wrap gap-2 items-center">
                    <button type="submit" class="bg-gray-800 text-white px-4 py-1.5 rounded-md text-sm hover:bg-gray-700">Apply Filters</button>
                    <a href="{{ route('research.index') }}" class="border border-gray-300 text-gray-600 px-4 py-1.5 rounded-md text-sm hover:bg-gray-50">Clear</a>
                    <span class="ml-auto text-xs text-gray-400 self-center">
                        {{ $products->total() }} product{{ $products->total() !== 1 ? 's' : '' }} found
                    </span>
                </div>
            </form>

            {{-- Bulk action bar --}}
            <form method="POST" action="{{ route('research.bulk-fetch-images') }}"
                  class="flex items-center gap-3 bg-amber-50 border border-amber-200 rounded-lg px-4 py-2.5 text-sm">
                @csrf
                <span class="text-amber-900">⚡ Auto-fetch images for products that don't have one yet (up to 30 per click).</span>
                <button type="submit"
                    onclick="this.innerHTML='⏳ Fetching… please wait'; this.disabled=true; this.form.submit();"
                    class="ml-auto bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold px-4 py-1.5 rounded-md whitespace-nowrap">
                    Bulk Fetch Images
                </button>
            </form>

            {{-- ======================================================
                 PRODUCT CARDS
            ====================================================== --}}
            @if ($products->isEmpty())
                <div class="text-center py-20 bg-white rounded-xl border border-gray-100 text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-lg font-medium">No research products found</p>
                    <p class="text-sm mt-1">Try clearing filters or add a new product above.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                    @foreach ($products as $product)
                        @php
                            $profit = $product->estimated_sell_price_bdt - $product->estimated_buy_price_bdt;
                            $margin = $product->estimated_buy_price_bdt > 0
                                ? round($profit / $product->estimated_buy_price_bdt * 100, 1)
                                : 0;
                            $riskColor = match($product->risk) {
                                'L' => 'bg-green-100 text-green-800',
                                'M' => 'bg-yellow-100 text-yellow-800',
                                'H' => 'bg-red-100 text-red-800',
                                default => 'bg-gray-100 text-gray-800',
                            };
                            $pipelineLabel = $product->pipeline === 'SA_TO_BD' ? 'SA → BD' : 'BD → SA';
                            $pipelineColor = $product->pipeline === 'SA_TO_BD' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800';
                        @endphp

                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex flex-col gap-3 hover:shadow-md transition-shadow">
                            {{-- Product Image --}}
                            <div class="w-full h-36 rounded-lg overflow-hidden border border-gray-100 flex items-center justify-center
                                {{ $product->imageSrc() ? 'bg-gray-50' : 'bg-gradient-to-br from-gray-50 to-gray-100' }}">
                                @if ($product->imageSrc())
                                    <img src="{{ $product->imageSrc() }}" alt="{{ $product->name }}"
                                        class="max-h-full max-w-full object-contain p-2"
                                        onerror="this.closest('.img-wrap').nextElementSibling && (this.closest('.img-wrap').nextElementSibling.style.display='flex'); this.parentElement.innerHTML='<span class=\'text-4xl font-black text-gray-200\'>{{ strtoupper(substr($product->name,0,1)) }}</span>';">
                                @else
                                    <div class="flex flex-col items-center gap-1 text-gray-300 select-none">
                                        <span class="text-5xl font-black text-gray-200 leading-none">{{ strtoupper(substr($product->name, 0, 1)) }}</span>
                                        <span class="text-xs text-gray-300">No image</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Get Image button (always visible when URL exists and no image yet) --}}
                            @if ($product->url && !$product->imageSrc())
                                <div x-data="{ pasteOpen: false }">
                                    <div class="flex gap-1.5 -mt-1">
                                        <form method="POST" action="{{ route('products.fetch-image', $product) }}" class="flex-1">
                                            @csrf
                                            <button type="submit"
                                                onclick="this.innerHTML='⏳ Fetching…'; this.disabled=true; this.form.submit();"
                                                class="w-full bg-indigo-50 border border-indigo-200 text-indigo-700 text-xs py-1.5 rounded-lg hover:bg-indigo-100 font-medium transition-colors">
                                                ⬇ Auto-fetch
                                            </button>
                                        </form>
                                        <button type="button" @click="pasteOpen = !pasteOpen"
                                            class="bg-gray-100 border border-gray-200 text-gray-600 text-xs px-3 py-1.5 rounded-lg hover:bg-gray-200 font-medium transition-colors whitespace-nowrap">
                                            📋 Paste URL
                                        </button>
                                    </div>
                                    <div x-show="pasteOpen" x-cloak x-transition class="mt-2">
                                        <form method="POST" action="{{ route('products.fetch-image', $product) }}" class="flex gap-1.5">
                                            @csrf
                                            <input type="url" name="image_url"
                                                placeholder="Right-click image on Amazon → Copy image address"
                                                class="flex-1 border border-gray-300 rounded-lg px-2.5 py-1.5 text-xs focus:ring-indigo-500 focus:border-indigo-500">
                                            <button type="submit" class="bg-indigo-600 text-white text-xs px-3 py-1.5 rounded-lg hover:bg-indigo-700 font-medium">Save</button>
                                        </form>
                                        <p class="text-xs text-gray-400 mt-1">On Amazon: right-click a product image → "Copy image address" → paste above</p>
                                    </div>
                                </div>
                            @elseif ($product->imageSrc() && $product->url)
                                <form method="POST" action="{{ route('products.fetch-image', $product) }}" class="-mt-1">
                                    @csrf
                                    <button type="submit"
                                        onclick="this.innerHTML='⏳…'; this.disabled=true; this.form.submit();"
                                        class="w-full text-xs text-gray-400 hover:text-indigo-600 py-0.5 transition-colors">
                                        ↻ Refresh image
                                    </button>
                                </form>
                            @endif

                            {{-- Header --}}
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-gray-900 text-sm leading-snug" title="{{ $product->name }}">
                                        {{ $product->name }}
                                    </h3>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $product->category }}</p>
                                </div>
                                <div class="flex flex-col items-end gap-1 shrink-0">
                                    <span class="inline-block text-xs font-medium px-2 py-0.5 rounded-full {{ $pipelineColor }}">{{ $pipelineLabel }}</span>
                                    <span class="inline-block text-xs font-medium px-2 py-0.5 rounded-full {{ $riskColor }}">
                                        {{ $product->risk === 'L' ? 'Low' : ($product->risk === 'M' ? 'Med' : 'High') }} Risk
                                    </span>
                                </div>
                            </div>

                            {{-- Stars --}}
                            <div class="flex items-center gap-1">
                                @php $stars = round($product->rating * 2) / 2; @endphp
                                @for ($i = 1; $i <= 5; $i++)
                                    <svg class="w-3.5 h-3.5 {{ $i <= $stars ? 'text-yellow-400' : 'text-gray-200' }} fill-current" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                                <span class="text-xs text-gray-500 ml-1">{{ number_format($product->rating, 1) }}</span>
                            </div>

                            {{-- Prices --}}
                            <div class="grid grid-cols-3 gap-1.5 text-center">
                                <div class="bg-gray-50 rounded-lg p-2">
                                    <p class="text-xs text-gray-400">Buy</p>
                                    <p class="font-semibold text-gray-800 text-sm">৳{{ number_format($product->estimated_buy_price_bdt) }}</p>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-2">
                                    <p class="text-xs text-gray-400">Sell</p>
                                    <p class="font-semibold text-gray-800 text-sm">৳{{ number_format($product->estimated_sell_price_bdt) }}</p>
                                </div>
                                <div class="{{ $profit > 0 ? 'bg-green-50' : 'bg-red-50' }} rounded-lg p-2">
                                    <p class="text-xs {{ $profit > 0 ? 'text-green-500' : 'text-red-500' }}">Profit</p>
                                    <p class="font-semibold {{ $profit > 0 ? 'text-green-700' : 'text-red-700' }} text-sm">৳{{ number_format($profit) }}</p>
                                    <p class="text-xs {{ $profit > 0 ? 'text-green-400' : 'text-red-400' }}">{{ $margin }}%</p>
                                </div>
                            </div>

                            @if ($product->weight_grams > 0)
                                <p class="text-xs text-gray-400">⚖ {{ number_format($product->weight_grams) }}g · {{ $product->source_market }}</p>
                            @else
                                <p class="text-xs text-gray-400">{{ $product->source_market }}</p>
                            @endif

                            @if ($product->notes)
                                <p class="text-xs text-gray-600 bg-amber-50 border border-amber-100 rounded-lg p-2 line-clamp-2" title="{{ $product->notes }}">
                                    {{ $product->notes }}
                                </p>
                            @endif

                            {{-- Actions --}}
                            <div class="flex gap-2 pt-2 border-t border-gray-100">
                                @if ($product->url)
                                    <a href="{{ $product->url }}" target="_blank" rel="noopener noreferrer"
                                        class="flex-1 text-center border border-gray-200 text-gray-600 text-xs py-1.5 rounded-md hover:bg-gray-50">
                                        View ↗
                                    </a>
                                @endif
                                <form method="POST" action="{{ route('research.activate', $product) }}" class="flex-1">
                                    @csrf
                                    <button type="submit"
                                        onclick="return confirm('Move \'{{ addslashes($product->name) }}\' to Active Products?')"
                                        class="w-full bg-indigo-600 text-white text-xs py-1.5 rounded-md hover:bg-indigo-700 font-medium">
                                        ✓ Activate
                                    </button>
                                </form>
                                <a href="{{ route('products.edit', $product) }}"
                                    class="border border-gray-200 text-gray-500 text-xs px-3 py-1.5 rounded-md hover:bg-gray-50">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('research.destroy', $product) }}">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        onclick="return confirm('Delete \'{{ addslashes($product->name) }}\' from research? This cannot be undone.')"
                                        title="Delete from research"
                                        class="border border-red-200 text-red-500 hover:bg-red-50 text-xs px-2.5 py-1.5 rounded-md">
                                        🗑
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">{{ $products->links() }}</div>
            @endif

        </div>
    </div>

    <script>
        function quickImport() {
            return {
                url: '',
                loading: false,
                result: null,
                async preview() {
                    if (!this.url.trim()) return;
                    this.loading = true;
                    this.result = null;
                    try {
                        const res = await fetch('{{ route('research.import-preview') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ url: this.url.trim() }),
                        });
                        this.result = await res.json();
                        if (!res.ok && !this.result.error) {
                            this.result.error = 'Request failed (HTTP ' + res.status + ').';
                        }
                    } catch (e) {
                        this.result = { error: 'Network error: ' + e.message };
                    } finally {
                        this.loading = false;
                    }
                },
                clear() { this.url = ''; this.result = null; },
                useThisData() {
                    if (!this.result || this.result.error) return;
                    // Notify outer x-data scope (it listens via @quick-import-use.window)
                    window.dispatchEvent(new CustomEvent('quick-import-use', {
                        detail: {
                            pipeline: this.result.pipeline,
                            name: this.result.name,
                        }
                    }));
                    const url = this.url.trim();
                    const result = this.result;
                    setTimeout(() => {
                        const form = document.querySelector('form[action="{{ route('research.store') }}"]');
                        if (!form) return;
                        const set = (name, val) => {
                            if (val === null || val === undefined || val === '') return;
                            const el = form.querySelector('[name="' + name + '"]');
                            if (el) {
                                el.value = val;
                                el.dispatchEvent(new Event('input', { bubbles: true }));
                                el.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                        };
                        set('name',          result.name);
                        set('category',      result.category);
                        set('url',           url);
                        set('image_url',     result.image);
                        set('source_market', result.pipeline === 'SA_TO_BD' ? 'Amazon.sa' : 'Daraz BD');
                        if (result.price_bdt) {
                            set('estimated_buy_price_bdt',  Math.round(result.price_bdt));
                            set('estimated_sell_price_bdt', Math.round(result.price_bdt * 1.4));
                        }
                        // Switch image tab to "Paste URL" so the image_url field is visible
                        form.querySelectorAll('button[type="button"]').forEach(b => {
                            if (b.textContent.trim() === 'Paste URL') b.click();
                        });
                        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }, 250);
                },
            }
        }
    </script>
</x-app-layout>
