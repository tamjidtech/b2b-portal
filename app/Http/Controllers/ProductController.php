<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        // Exclude research-status products — they belong on the Research board
        $query = Product::where('status', '!=', 'research');
        if ($request->filled('pipeline')) {
            $query->where('pipeline', $request->pipeline);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('category', 'like', '%' . $request->search . '%');
            });
        }
        $products = $query->orderByDesc('rating')->paginate(20)->withQueryString();
        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.form', ['product' => new Product]);
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
            'status'                  => 'required|in:research,active,paused,discontinued',
            'estimated_buy_price_bdt' => 'required|numeric|min:0',
            'estimated_sell_price_bdt'=> 'required|numeric|min:0',
            'weight_grams'            => 'nullable|integer|min:0',
        ]);

        $data['image'] = $this->resolveImage($request, null);
        unset($data['image_file'], $data['image_url']);

        Product::create($data);
        return redirect()->route('products.index')->with('success', 'Product added.');
    }

    public function show(Product $product)
    {
        $product->load('purchases.trip', 'sales.purchase');
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        return view('products.form', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name'                    => 'required|string|max:255',
            'category'                => 'required|string|max:100',
            'pipeline'                => 'required|in:SA_TO_BD,BD_TO_SA',
            'source_market'           => 'required|string|max:100',
            'url'                     => 'nullable|url|max:500',
            'image_file'              => 'nullable|image|max:4096',
            'image_url'               => 'nullable|url|max:500',
            'remove_image'            => 'nullable|boolean',
            'notes'                   => 'nullable|string',
            'rating'                  => 'nullable|numeric|min:0|max:5',
            'risk'                    => 'required|in:L,M,H',
            'status'                  => 'required|in:research,active,paused,discontinued',
            'estimated_buy_price_bdt' => 'required|numeric|min:0',
            'estimated_sell_price_bdt'=> 'required|numeric|min:0',
            'weight_grams'            => 'nullable|integer|min:0',
        ]);

        if ($request->boolean('remove_image')) {
            $this->deleteStoredImage($product->image);
            $data['image'] = null;
        } else {
            $data['image'] = $this->resolveImage($request, $product->image);
        }
        unset($data['image_file'], $data['image_url'], $data['remove_image']);

        $product->update($data);
        return redirect()->route('products.show', $product)->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $this->deleteStoredImage($product->image);
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted.');
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    private function resolveImage(Request $request, ?string $current): ?string
    {
        if ($request->hasFile('image_file')) {
            $this->deleteStoredImage($current);
            return $request->file('image_file')->store('products', 'public');
        }
        if ($request->filled('image_url')) {
            $this->deleteStoredImage($current);
            return $request->input('image_url');
        }
        return $current;
    }

    private function deleteStoredImage(?string $image): void
    {
        if ($image && !str_starts_with($image, 'http') && Storage::disk('public')->exists($image)) {
            Storage::disk('public')->delete($image);
        }
    }
}
