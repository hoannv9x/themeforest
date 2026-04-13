<?php

namespace Database\Seeders;

use App\Models\Prediction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PredictionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Prediction::factory(10)->create(
            [
                'date' => now(),
                'number' => json_encode([1, 2, 3]),
                'region' => 'mb',
                'algorithm' => 'xxx',
                'accuracy' => random_int(1, 100),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}
