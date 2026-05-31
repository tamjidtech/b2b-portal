<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'product_id', 'purchase_id', 'quantity',
        'unit_price_bdt', 'platform_fee_bdt', 'delivery_cost_bdt',
        'platform', 'sale_date', 'buyer_ref', 'notes',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'unit_price_bdt' => 'float',
        'platform_fee_bdt' => 'float',
        'delivery_cost_bdt' => 'float',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function grossRevenue(): float
    {
        return $this->unit_price_bdt * $this->quantity;
    }

    public function deductions(): float
    {
        return $this->platform_fee_bdt + $this->delivery_cost_bdt;
    }

    public function netRevenue(): float
    {
        return $this->grossRevenue() - $this->deductions();
    }

    public function costOfGoods(): float
    {
        if ($this->purchase) {
            return $this->purchase->allInUnitCost() * $this->quantity;
        }
        return $this->product
            ? $this->product->estimated_buy_price_bdt * $this->quantity
            : 0;
    }

    public function netProfit(): float
    {
        return $this->netRevenue() - $this->costOfGoods();
    }
}
