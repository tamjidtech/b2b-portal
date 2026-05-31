<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->string('name');                         // e.g. "May 2026 KSA Run"
            $table->enum('direction', ['SA_TO_BD', 'BD_TO_SA']);
            $table->date('trip_date');
            $table->decimal('luggage_weight_kg', 8, 2)->default(0);
            $table->decimal('flight_cost_bdt', 12, 2)->default(0);
            $table->decimal('extra_baggage_cost_bdt', 12, 2)->default(0);
            $table->decimal('other_cost_bdt', 12, 2)->default(0);  // visa, hotel, etc.
            $table->text('notes')->nullable();
            $table->enum('status', ['planned', 'completed', 'cancelled'])->default('planned');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
