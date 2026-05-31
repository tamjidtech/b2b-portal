<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Trip;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $query = Purchase::with('product', 'trip');
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        $purchases = $query->orderByDesc('purchase_date')->paginate(20)->withQueryString();
        $products  = Product::orderBy('name')->get();
        return view('purchases.index', compact('purchases', 'products'));
    }

    public function create()
    {
        $products = Product::where('status', 'active')->orderBy('name')->get();
        $trips    = Trip::whereIn('status', ['planned', 'completed'])->orderByDesc('trip_date')->get();
        return view('purchases.form', ['purchase' => new Purchase, 'products' => $products, 'trips' => $trips]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id'        => 'required|exists:products,id',
            'trip_id'           => 'nullable|exists:trips,id',
            'quantity'          => 'required|integer|min:1',
            'unit_cost_bdt'     => 'required|numeric|min:0',
            'shipping_cost_bdt' => 'nullable|numeric|min:0',
            'customs_cost_bdt'  => 'nullable|numeric|min:0',
            'other_cost_bdt'    => 'nullable|numeric|min:0',
            'purchase_date'     => 'required|date',
            'invoice_ref'       => 'nullable|string|max:100',
            'notes'             => 'nullable|string',
        ]);
        Purchase::create($data);
        return redirect()->route('purchases.index')->with('success', 'Purchase recorded.');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load('product', 'trip', 'sales');
        return view('purchases.show', compact('purchase'));
    }

    public function edit(Purchase $purchase)
    {
        $products = Product::where('status', 'active')->orderBy('name')->get();
        $trips    = Trip::whereIn('status', ['planned', 'completed'])->orderByDesc('trip_date')->get();
        return view('purchases.form', compact('purchase', 'products', 'trips'));
    }

    public function update(Request $request, Purchase $purchase)
    {
        $data = $request->validate([
            'product_id'        => 'required|exists:products,id',
            'trip_id'           => 'nullable|exists:trips,id',
            'quantity'          => 'required|integer|min:1',
            'unit_cost_bdt'     => 'required|numeric|min:0',
            'shipping_cost_bdt' => 'nullable|numeric|min:0',
            'customs_cost_bdt'  => 'nullable|numeric|min:0',
            'other_cost_bdt'    => 'nullable|numeric|min:0',
            'purchase_date'     => 'required|date',
            'invoice_ref'       => 'nullable|string|max:100',
            'notes'             => 'nullable|string',
        ]);
        $purchase->update($data);
        return redirect()->route('purchases.show', $purchase)->with('success', 'Purchase updated.');
    }

    public function destroy(Purchase $purchase)
    {
        $purchase->delete();
        return redirect()->route('purchases.index')->with('success', 'Purchase deleted.');
    }
}
