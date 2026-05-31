<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ProductScraperService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ResearchController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::where('status', 'research');

        if ($request->filled('pipeline')) {
            $query->where('pipeline', $request->pipeline);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('risk')) {
            $query->where('risk', $request->risk);
        }
        if ($request->filled('min_rating')) {
            $query->where('rating', '>=', $request->min_rating);
        }
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->boolean('has_image')) {
            $query->whereNotNull('image')->where('image', '!=', '');
        }
        if ($request->boolean('no_image')) {
            $query->where(fn($q) => $q->whereNull('image')->orWhere('image', ''));
        }

        $sort = $request->input('sort', 'rating_desc');
        match ($sort) {
            'rating_asc'  => $query->orderBy('rating'),
            'profit_desc' => $query->orderByRaw('(estimated_sell_price_bdt - estimated_buy_price_bdt) DESC'),
            'profit_asc'  => $query->orderByRaw('(estimated_sell_price_bdt - estimated_buy_price_bdt) ASC'),
            'margin_desc' => $query->orderByRaw('CASE WHEN estimated_buy_price_bdt > 0 THEN ((estimated_sell_price_bdt - estimated_buy_price_bdt) / estimated_buy_price_bdt) ELSE 0 END DESC'),
            'newest'      => $query->orderByDesc('created_at'),
            'oldest'      => $query->orderBy('created_at'),
            'name_asc'    => $query->orderBy('name'),
            default       => $query->orderByDesc('rating'),
        };

        $products = $query->paginate(24)->withQueryString();

        $categories = Product::where('status', 'research')
            ->distinct()->pluck('category')->sort()->values();

        $stats = [
            'total'      => Product::where('status', 'research')->count(),
            'with_image' => Product::where('status', 'research')->whereNotNull('image')->where('image', '!=', '')->count(),
            'high_rated' => Product::where('status', 'research')->where('rating', '>=', 4.0)->count(),
            'low_risk'   => Product::where('status', 'research')->where('risk', 'L')->count(),
        ];

        return view('research.index', compact('products', 'categories', 'stats', 'sort'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                    => 'required|string|max:255',
            'category'                => 'required|string|max:100',
            'pipeline'                => 'required|in:SA_TO_BD,BD_TO_SA',
            'source_market'           => 'required|string|max:100',
            'url'                     => 'nullable|url|max:500',
            'image_file'              => 'nullable|image|max:4096',
            'image_url'               => 'nullable|url|max:500',
            'notes'                   => 'nullable|string',
            'rating'                  => 'nullable|numeric|min:0|max:5',
            'risk'                    => 'required|in:L,M,H',
            'estimated_buy_price_bdt' => 'required|numeric|min:0',
            'estimated_sell_price_bdt'=> 'required|numeric|min:0',
            'weight_grams'            => 'nullable|integer|min:0',
        ]);

        if ($duplicate = $this->findDuplicate($data['name'], $data['pipeline'])) {
            $statusLabel = $duplicate->status === 'research' ? 'already in Research' : "already an {$duplicate->status} product";
            return back()
                ->withInput()
                ->withErrors(['name' => "Looks like a duplicate of \"{$duplicate->name}\" — {$statusLabel}."])
                ->with('open_add_form', true);
        }

        if ($request->hasFile('image_file')) {
            $data['image'] = $request->file('image_file')->store('products', 'public');
        } elseif ($request->filled('image_url')) {
            $data['image'] = $request->input('image_url');
        }

        $data['status'] = 'research';
        $data['rating'] = $data['rating'] ?? 0;
        $data['weight_grams'] = $data['weight_grams'] ?? 0;
        unset($data['image_file'], $data['image_url']);

        Product::create($data);

        return redirect()->route('research.index')
            ->with('success', "\"{$data['name']}\" added to Research board.");
    }

    /**
     * Quick Import — fetches a product page and returns extracted fields as JSON
     * for the front-end to pre-fill the add-product form.
     */
    public function importPreview(Request $request, ProductScraperService $scraper)
    {
        $request->validate(['url' => 'required|url|max:500']);

        $url = $request->input('url');
        $data = $scraper->fetch($url);
        $host = parse_url($url, PHP_URL_HOST) ?? '';
        $pipeline = str_contains($host, 'amazon.sa') || str_contains($host, 'noon.com/saudi')
            ? 'SA_TO_BD' : 'BD_TO_SA';

        $duplicateInfo = null;
        if ($data['name']) {
            if ($dup = $this->findDuplicate($data['name'], $pipeline)) {
                $duplicateInfo = [
                    'name'   => $dup->name,
                    'status' => $dup->status,
                ];
            }
        }

        return response()->json([
            'success'     => $data['error'] === null,
            'error'       => $data['error'],
            'name'        => $data['name'],
            'image'       => $data['image'],
            'price_bdt'   => $data['price_bdt'],
            'price_local' => $data['price_local'],
            'currency'    => $data['currency'],
            'category'    => $data['category'],
            'source'      => $data['source'],
            'pipeline'    => $pipeline,
            'duplicate'   => $duplicateInfo,
        ]);
    }

    /**
     * Bulk fetch images for research products with URLs but no image yet.
     */
    public function bulkFetchImages(ProductScraperService $scraper)
    {
        $products = Product::where('status', 'research')
            ->whereNotNull('url')
            ->where(function ($q) {
                $q->whereNull('image')->orWhere('image', '');
            })
            ->limit(30)
            ->get();

        if ($products->isEmpty()) {
            return back()->with('error', 'No products needing images (all already have images, or none have URLs).');
        }

        $fetched = 0;
        $failed  = 0;
        foreach ($products as $product) {
            $data = $scraper->fetch($product->url);
            if (!empty($data['image'])) {
                $product->update(['image' => $data['image']]);
                $fetched++;
            } else {
                $failed++;
            }
        }

        $msg = "Bulk fetch: {$fetched} images saved" . ($failed > 0 ? ", {$failed} could not be fetched (sites may block scraping)" : '.');
        return back()->with($fetched > 0 ? 'success' : 'error', $msg);
    }

    public function activate(Product $product)
    {
        abort_unless($product->status === 'research', 403, 'Product is not in research status.');
        $product->update(['status' => 'active']);

        return redirect()->route('research.index')
            ->with('success', "\"{$product->name}\" moved to active products.");
    }

    public function destroy(Product $product)
    {
        abort_unless($product->status === 'research', 403, 'Only research products can be deleted from this page.');

        if ($product->image && !str_starts_with($product->image, 'http')) {
            Storage::disk('public')->delete($product->image);
        }
        $name = $product->name;
        $product->delete();

        return back()->with('success', "\"{$name}\" removed from Research board.");
    }

    /**
     * Smart duplicate detection — case-insensitive exact match first,
     * then fuzzy substring match with 70% similarity threshold.
     */
    private function findDuplicate(string $name, string $pipeline): ?Product
    {
        $normalized = strtolower(trim($name));

        $exact = Product::whereRaw('LOWER(name) = ?', [$normalized])
            ->where('pipeline', $pipeline)
            ->first();
        if ($exact) return $exact;

        if (mb_strlen($normalized) >= 6) {
            $candidates = Product::where('pipeline', $pipeline)
                ->where(function ($q) use ($normalized) {
                    $q->whereRaw('LOWER(name) LIKE ?', ['%' . $normalized . '%'])
                      ->orWhereRaw("? LIKE CONCAT('%', LOWER(name), '%')", [$normalized]);
                })
                ->get();
            foreach ($candidates as $c) {
                similar_text($normalized, strtolower($c->name), $percent);
                if ($percent >= 70) return $c;
            }
        }

        return null;
    }
}
