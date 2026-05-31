<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add 'research' to the status enum on the products table.
     * Uses raw ALTER TABLE for MySQL compatibility (FK-safe, no drop needed).
     */
    public function up(): void
    {
        // For MySQL: modify enum column in place (FK-safe)
        if (config('database.default') === 'mysql') {
            \DB::statement("ALTER TABLE products MODIFY COLUMN status ENUM('research','active','paused','discontinued') NOT NULL DEFAULT 'research'");
        } else {
            // SQLite fallback: recreate the table (SQLite does not support ALTER COLUMN)
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
    }

    public function down(): void
    {
        if (config('database.default') === 'mysql') {
            \DB::statement("ALTER TABLE products MODIFY COLUMN status ENUM('active','paused','discontinued') NOT NULL DEFAULT 'active'");
        } else {
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
    }
};
