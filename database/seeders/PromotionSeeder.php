<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sales\Promotion;

class PromotionSeeder extends Seeder
{
    public function run(): void
    {
        Promotion::create([
            'name' => 'Summer Sale 10%',
            'code' => 'SUMMER10',
            'type' => 'percentage',
            'value' => 10.00,
            'minimum_order_amount' => 500.00,
            'usage_limit' => 100,
            'starts_at' => now(),
            'expires_at' => now()->addDays(30),
            'is_active' => true,
        ]);
    }
}
