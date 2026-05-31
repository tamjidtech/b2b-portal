<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    protected $fillable = [
        'name', 'direction', 'trip_date', 'luggage_weight_kg',
        'flight_cost_bdt', 'extra_baggage_cost_bdt', 'other_cost_bdt',
        'notes', 'status',
    ];

    protected $casts = [
        'trip_date' => 'date',
        'flight_cost_bdt' => 'float',
        'extra_baggage_cost_bdt' => 'float',
        'other_cost_bdt' => 'float',
        'luggage_weight_kg' => 'float',
    ];

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function totalTripCost(): float
    {
        return $this->flight_cost_bdt + $this->extra_baggage_cost_bdt + $this->other_cost_bdt;
    }

    public function totalGoodsCost(): float
    {
        return (float) $this->purchases()
            ->selectRaw('SUM(quantity * unit_cost_bdt) as c')
            ->value('c') ?? 0;
    }

    public function totalInvested(): float
    {
        return $this->totalTripCost() + $this->totalGoodsCost();
    }
}
