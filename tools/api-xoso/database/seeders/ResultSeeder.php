<?php

namespace Database\Seeders;

use App\Models\Result;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ResultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Result::factory(10)->create(
            [
                'date' => now(),
                'region' => 'mb',
                'province_code' => 'mb',
                'raw_data' => json_encode([1, 2, 3]),
                'created_at' => now(),
            ]
        );
    }
}
