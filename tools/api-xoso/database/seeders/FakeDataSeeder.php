<?php

namespace Database\Seeders;

use App\Models\Number;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FakeDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 100; $i++) {
            Number::create([
                'result_id' => 1,
                'prize' => 0,
                'number' => str_pad(rand(0, 99), 2, '0', STR_PAD_LEFT)
            ]);
        }
    }
}
