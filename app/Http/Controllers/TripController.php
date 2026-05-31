<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use Illuminate\Http\Request;

class TripController extends Controller
{
    public function index()
    {
        $trips = Trip::withCount('purchases')->orderByDesc('trip_date')->paginate(15);
        return view('trips.index', compact('trips'));
    }

    public function create()
    {
        return view('trips.form', ['trip' => new Trip]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                   => 'required|string|max:255',
            'direction'              => 'required|in:SA_TO_BD,BD_TO_SA',
            'trip_date'              => 'required|date',
            'luggage_weight_kg'      => 'nullable|numeric|min:0',
            'flight_cost_bdt'        => 'nullable|numeric|min:0',
            'extra_baggage_cost_bdt' => 'nullable|numeric|min:0',
            'other_cost_bdt'         => 'nullable|numeric|min:0',
            'notes'                  => 'nullable|string',
            'status'                 => 'required|in:planned,completed,cancelled',
        ]);
        Trip::create($data);
        return redirect()->route('trips.index')->with('success', 'Trip created.');
    }

    public function show(Trip $trip)
    {
        $trip->load('purchases.product');
        return view('trips.show', compact('trip'));
    }

    public function edit(Trip $trip)
    {
        return view('trips.form', compact('trip'));
    }

    public function update(Request $request, Trip $trip)
    {
        $data = $request->validate([
            'name'                   => 'required|string|max:255',
            'direction'              => 'required|in:SA_TO_BD,BD_TO_SA',
            'trip_date'              => 'required|date',
            'luggage_weight_kg'      => 'nullable|numeric|min:0',
            'flight_cost_bdt'        => 'nullable|numeric|min:0',
            'extra_baggage_cost_bdt' => 'nullable|numeric|min:0',
            'other_cost_bdt'         => 'nullable|numeric|min:0',
            'notes'                  => 'nullable|string',
            'status'                 => 'required|in:planned,completed,cancelled',
        ]);
        $trip->update($data);
        return redirect()->route('trips.show', $trip)->with('success', 'Trip updated.');
    }

    public function destroy(Trip $trip)
    {
        $trip->delete();
        return redirect()->route('trips.index')->with('success', 'Trip deleted.');
    }
}
