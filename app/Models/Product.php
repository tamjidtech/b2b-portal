<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name', 'category', 'pipeline', 'source_market', 'url', 'image',
        'notes', 'rating', 'risk', 'status',
        'estimated_buy_price_bdt', 'estimated_sell_price_bdt', 'weight_grams',
    ];

    /**
     * Returns a displayable image src — external URL or storage path.
     */
    public function imageSrc(): ?string
    {
        if (!$this->image) return null;
        if (str_starts_with($this->image, 'http')) return $this->image;
        return asset('storage/' . $this->image);
    }

    protected $casts = [
        'rating' => 'float',
        'estimated_buy_price_bdt' => 'float',
        'estimated_sell_price_bdt' => 'float',
    ];

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function estimatedProfitPerUnit(): float
    {
        return $this->estimated_sell_price_bdt - $this->estimated_buy_price_bdt;
    }

    public function totalUnitsPurchased(): int
    {
        return (int) $this->purchases()->sum('quantity');
    }

    public function totalUnitsSold(): int
    {
        return (int) $this->sales()->sum('quantity');
    }

    public function stockInHand(): int
    {
        return $this->totalUnitsPurchased() - $this->totalUnitsSold();
    }

    public function totalRevenue(): float
    {
        return (float) $this->sales()
            ->selectRaw('SUM(quantity * unit_price_bdt) as rev')
            ->value('rev') ?? 0;
    }

    public function totalCost(): float
    {
        return (float) $this->purchases()
            ->selectRaw('SUM(quantity * unit_cost_bdt + shipping_cost_bdt + customs_cost_bdt + other_cost_bdt) as cost')
            ->value('cost') ?? 0;
    }

    public function totalNetProfit(): float
    {
        $salesDeductions = (float) $this->sales()
            ->selectRaw('SUM(platform_fee_bdt + delivery_cost_bdt) as ded')
            ->value('ded') ?? 0;
        return $this->totalRevenue() - $this->totalCost() - $salesDeductions;
    }
}
