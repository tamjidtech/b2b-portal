<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with('product', 'purchase');
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        $sales    = $query->orderByDesc('sale_date')->paginate(20)->withQueryString();
        $products = Product::orderBy('name')->get();
        return view('sales.index', compact('sales', 'products'));
    }

    public function create(Request $request)
    {
        $products  = Product::where('status', 'active')->orderBy('name')->get();
        $purchases = Purchase::with('product')
            ->whereRaw('quantity > (SELECT COALESCE(SUM(s.quantity),0) FROM sales s WHERE s.purchase_id = purchases.id)')
            ->orderByDesc('purchase_date')
            ->get();
        $selectedProduct = $request->product_id ? Product::find($request->product_id) : null;
        return view('sales.form', [
            'sale'      => new Sale,
            'products'  => $products,
            'purchases' => $purchases,
            'selectedProduct' => $selectedProduct,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id'       => 'required|exists:products,id',
            'purchase_id'      => 'nullable|exists:purchases,id',
            'quantity'         => 'required|integer|min:1',
            'unit_price_bdt'   => 'required|numeric|min:0',
            'platform_fee_bdt' => 'nullable|numeric|min:0',
            'delivery_cost_bdt'=> 'nullable|numeric|min:0',
            'platform'         => 'nullable|string|max:100',
            'sale_date'        => 'required|date',
            'buyer_ref'        => 'nullable|string|max:100',
            'notes'            => 'nullable|string',
        ]);
        Sale::create($data);
        return redirect()->route('sales.index')->with('success', 'Sale recorded.');
    }

    public function show(Sale $sale)
    {
        $sale->load('product', 'purchase');
        return view('sales.show', compact('sale'));
    }

    public function edit(Sale $sale)
    {
        $products  = Product::where('status', 'active')->orderBy('name')->get();
        $purchases = Purchase::with('product')->orderByDesc('purchase_date')->get();
        return view('sales.form', compact('sale', 'products', 'purchases'));
    }

    public function update(Request $request, Sale $sale)
    {
        $data = $request->validate([
            'product_id'       => 'required|exists:products,id',
            'purchase_id'      => 'nullable|exists:purchases,id',
            'quantity'         => 'required|integer|min:1',
            'unit_price_bdt'   => 'required|numeric|min:0',
            'platform_fee_bdt' => 'nullable|numeric|min:0',
            'delivery_cost_bdt'=> 'nullable|numeric|min:0',
            'platform'         => 'nullable|string|max:100',
            'sale_date'        => 'required|date',
            'buyer_ref'        => 'nullable|string|max:100',
            'notes'            => 'nullable|string',
        ]);
        $sale->update($data);
        return redirect()->route('sales.show', $sale)->with('success', 'Sale updated.');
    }

    public function destroy(Sale $sale)
    {
        $sale->delete();
        return redirect()->route('sales.index')->with('success', 'Sale deleted.');
    }
}
