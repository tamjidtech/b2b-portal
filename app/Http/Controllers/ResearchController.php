<?php

namespace App\Http\Controllers;

use App\Models\Product;
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

        $products = $query->orderByDesc('rating')->paginate(24)->withQueryString();

        $categories = Product::where('status', 'research')
            ->distinct()->pluck('category')->sort()->values();

        return view('research.index', compact('products', 'categories'));
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

        // Duplicate detection — case-insensitive name match in same pipeline
        $duplicate = Product::whereRaw('LOWER(name) = ?', [strtolower($data['name'])])
            ->where('pipeline', $data['pipeline'])
            ->first();

        if ($duplicate) {
            $statusLabel = $duplicate->status === 'research' ? 'already in Research' : "already an {$duplicate->status} product";
            return back()
                ->withInput()
                ->withErrors(['name' => "A product named \"{$duplicate->name}\" is {$statusLabel} ({$duplicate->pipeline})."])
                ->with('open_add_form', true);
        }

        // Resolve image: uploaded file takes priority over URL
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

    public function activate(Product $product)
    {
        abort_unless($product->status === 'research', 403, 'Product is not in research status.');
        $product->update(['status' => 'active']);

        return redirect()->route('research.index')
            ->with('success', "\"{$product->name}\" moved to active products.");
    }
}
