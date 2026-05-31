<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">{{ $trip->exists ? 'Edit Trip' : 'Add Trip' }}</h2>
    </x-slot>
    <div class="py-8 max-w-2xl mx-auto px-4">
        <div class="bg-white shadow rounded-xl p-6">
            <form method="POST" action="{{ $trip->exists ? route('trips.update', $trip) : route('trips.store') }}">
                @csrf
                @if($trip->exists) @method('PATCH') @endif
                @if($errors->any())
                    <div class="mb-4 bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded text-sm">
                        <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Trip Name *</label>
                        <input name="name" value="{{ old('name', $trip->name) }}" placeholder="e.g. June 2026 KSA Run" class="w-full border rounded px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Direction *</label>
                        <select name="direction" class="w-full border rounded px-3 py-2" required>
                            <option value="SA_TO_BD" @selected(old('direction', $trip->direction)=='SA_TO_BD')>SA → BD</option>
                            <option value="BD_TO_SA" @selected(old('direction', $trip->direction)=='BD_TO_SA')>BD → SA</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Trip Date *</label>
                        <input name="trip_date" type="date" value="{{ old('trip_date', $trip->trip_date?->format('Y-m-d')) }}" class="w-full border rounded px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                        <select name="status" class="w-full border rounded px-3 py-2" required>
                            <option value="planned" @selected(old('status', $trip->status)=='planned')>Planned</option>
                            <option value="completed" @selected(old('status', $trip->status)=='completed')>Completed</option>
                            <option value="cancelled" @selected(old('status', $trip->status)=='cancelled')>Cancelled</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Luggage Weight (kg)</label>
                        <input name="luggage_weight_kg" type="number" step="0.1" value="{{ old('luggage_weight_kg', $trip->luggage_weight_kg) }}" class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Flight Cost (BDT)</label>
                        <input name="flight_cost_bdt" type="number" step="0.01" value="{{ old('flight_cost_bdt', $trip->flight_cost_bdt) }}" class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Extra Baggage Cost (BDT)</label>
                        <input name="extra_baggage_cost_bdt" type="number" step="0.01" value="{{ old('extra_baggage_cost_bdt', $trip->extra_baggage_cost_bdt) }}" class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Other Costs (BDT)</label>
                        <input name="other_cost_bdt" type="number" step="0.01" value="{{ old('other_cost_bdt', $trip->other_cost_bdt) }}" placeholder="Visa, hotel, transport…" class="w-full border rounded px-3 py-2">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea name="notes" rows="3" class="w-full border rounded px-3 py-2">{{ old('notes', $trip->notes) }}</textarea>
                    </div>
                </div>
                <div class="mt-5 flex gap-3">
                    <button type="submit" class="bg-purple-600 text-white px-5 py-2 rounded-lg hover:bg-purple-700">{{ $trip->exists ? 'Update' : 'Add Trip' }}</button>
                    <a href="{{ route('trips.index') }}" class="px-5 py-2 border rounded-lg text-gray-600 hover:bg-gray-50">Cancel</a>
                    @if($trip->exists)
                    <form method="POST" action="{{ route('trips.destroy', $trip) }}" class="ml-auto" onsubmit="return confirm('Delete this trip?')">
                        @csrf @method('DELETE')
                        <button class="text-red-600 hover:underline text-sm">Delete</button>
                    </form>
                    @endif
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
