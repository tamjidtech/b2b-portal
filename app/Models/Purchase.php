<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'product_id', 'trip_id', 'quantity',
        'unit_cost_bdt', 'shipping_cost_bdt', 'customs_cost_bdt', 'other_cost_bdt',
        'purchase_date', 'invoice_ref', 'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'unit_cost_bdt' => 'float',
        'shipping_cost_bdt' => 'float',
        'customs_cost_bdt' => 'float',
        'other_cost_bdt' => 'float',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function totalCostBdt(): float
    {
        return ($this->unit_cost_bdt * $this->quantity)
            + $this->shipping_cost_bdt
            + $this->customs_cost_bdt
            + $this->other_cost_bdt;
    }

    public function allInUnitCost(): float
    {
        return $this->quantity > 0
            ? $this->totalCostBdt() / $this->quantity
            : 0;
    }

    public function unitsSold(): int
    {
        return (int) $this->sales()->sum('quantity');
    }

    public function unitsRemaining(): int
    {
        return $this->quantity - $this->unitsSold();
    }
}
