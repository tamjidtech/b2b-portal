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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trip_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('quantity');
            $table->decimal('unit_cost_bdt', 12, 2);       // price paid per unit
            $table->decimal('shipping_cost_bdt', 12, 2)->default(0);  // portion allocated
            $table->decimal('customs_cost_bdt', 12, 2)->default(0);
            $table->decimal('other_cost_bdt', 12, 2)->default(0);
            $table->date('purchase_date');
            $table->string('invoice_ref')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
