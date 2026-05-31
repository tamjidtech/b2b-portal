<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Recreate products table to add 'research' to status enum.
     * SQLite does not support ALTER COLUMN, so we drop and recreate.
     * Safe: no product data exists at this migration point.
     */
    public function up(): void
    {
        Schema::drop('products');
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');
            $table->enum('pipeline', ['SA_TO_BD', 'BD_TO_SA']);
            $table->string('source_market');
            $table->string('url')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('rating', 3, 1)->default(0);
            $table->enum('risk', ['L', 'M', 'H'])->default('M');
            $table->enum('status', ['research', 'active', 'paused', 'discontinued'])->default('research');
            $table->decimal('estimated_buy_price_bdt', 12, 2)->default(0);
            $table->decimal('estimated_sell_price_bdt', 12, 2)->default(0);
            $table->integer('weight_grams')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::drop('products');
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');
            $table->enum('pipeline', ['SA_TO_BD', 'BD_TO_SA']);
            $table->string('source_market');
            $table->string('url')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('rating', 3, 1)->default(0);
            $table->enum('risk', ['L', 'M', 'H'])->default('M');
            $table->enum('status', ['active', 'paused', 'discontinued'])->default('active');
            $table->decimal('estimated_buy_price_bdt', 12, 2)->default(0);
            $table->decimal('estimated_sell_price_bdt', 12, 2)->default(0);
            $table->integer('weight_grams')->default(0);
            $table->timestamps();
        });
    }
};
