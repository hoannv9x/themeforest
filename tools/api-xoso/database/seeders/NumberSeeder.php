<?php

namespace Database\Seeders;

use App\Models\Number;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NumberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Number::factory(10)->create(
            [
                'result_id' => 1,
                'number' => random_int(10, 100),
                'prize' => 'g1'
            ]
        );
    }
}
