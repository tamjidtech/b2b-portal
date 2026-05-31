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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('quantity');
            $table->decimal('unit_price_bdt', 12, 2);      // selling price per unit
            $table->decimal('platform_fee_bdt', 12, 2)->default(0);  // Daraz/FB commission
            $table->decimal('delivery_cost_bdt', 12, 2)->default(0); // local delivery
            $table->string('platform')->nullable();          // FB, Daraz, Direct, etc.
            $table->date('sale_date');
            $table->string('buyer_ref')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
