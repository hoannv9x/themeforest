<?php

namespace Database\Seeders;

use App\Models\Number;
use App\Models\NumberStat;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NumberStatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $regions = Number::REGIONS;
        foreach ($regions as $region) {
            for ($i = 0; $i < 100; $i++) {
                NumberStat::firstOrCreate([
                    'number' => str_pad($i, 2, '0', STR_PAD_LEFT),
                    'region' => $region,
                ], [
                    'total_count' => 0,
                    'last_appeared_at' => now(),
                    'updated_at' => now(),
                    'current_gap' => 0
                ]);
            }
        }
    }
}
