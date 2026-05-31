<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $file = database_path('seeders/products.json');
        if (! file_exists($file)) {
            $this->command->error("products.json not found at {$file}");
            return;
        }

        $products = json_decode(file_get_contents($file), true);
        $now      = now();

        // Truncate existing research products so seeder is idempotent
        \DB::table('products')->where('status', 'research')->delete();

        $rows = array_map(fn($p) => array_merge($p, [
            'created_at' => $now,
            'updated_at' => $now,
        ]), $products);

        foreach (array_chunk($rows, 50) as $chunk) {
            \DB::table('products')->insert($chunk);
        }

        $this->command->info('Seeded ' . count($products) . ' research products.');
    }
}
