<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Trip;

class DashboardController extends Controller
{
    public function index()
    {
        $totalRevenue   = Sale::selectRaw('SUM(quantity * unit_price_bdt) as r')->value('r') ?? 0;
        $totalDeductions= Sale::selectRaw('SUM(platform_fee_bdt + delivery_cost_bdt) as d')->value('d') ?? 0;
        $totalCogs      = Purchase::selectRaw(
            'SUM(quantity * unit_cost_bdt + shipping_cost_bdt + customs_cost_bdt + other_cost_bdt) as c'
        )->value('c') ?? 0;
        $tripCosts      = Trip::where('status', 'completed')
            ->selectRaw('SUM(flight_cost_bdt + extra_baggage_cost_bdt + other_cost_bdt) as t')
            ->value('t') ?? 0;

        $grossProfit  = $totalRevenue - $totalCogs - $totalDeductions;
        $netProfit    = $grossProfit - $tripCosts;

        $productCount  = Product::where('status', 'active')->count();
        $tripCount     = Trip::where('status', 'completed')->count();
        $pendingTrips  = Trip::where('status', 'planned')->count();

        $inventoryValue = 0;
        foreach (Product::with('purchases.sales')->get() as $p) {
            $inventoryValue += $p->stockInHand() * $p->estimated_sell_price_bdt;
        }

        $recentSales     = Sale::with('product')->orderByDesc('sale_date')->limit(10)->get();
        $recentPurchases = Purchase::with('product', 'trip')->orderByDesc('purchase_date')->limit(10)->get();
        $topProducts     = Product::all()->sortByDesc(fn($p) => $p->totalNetProfit())->take(5);

        $dateFormat = config('database.default') === 'sqlite'
            ? "strftime('%Y-%m', sale_date)"
            : "DATE_FORMAT(sale_date, '%Y-%m')";
        $monthlyRevenue  = Sale::selectRaw("$dateFormat as month, SUM(quantity * unit_price_bdt) as revenue")
            ->groupBy('month')->orderBy('month')->limit(6)->pluck('revenue', 'month');

        return view('dashboard', compact(
            'totalRevenue', 'totalCogs', 'totalDeductions', 'tripCosts',
            'grossProfit', 'netProfit', 'productCount', 'tripCount', 'pendingTrips',
            'inventoryValue', 'recentSales', 'recentPurchases', 'topProducts', 'monthlyRevenue'
        ));
    }
}

