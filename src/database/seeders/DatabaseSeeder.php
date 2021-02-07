<?php

namespace Database\Seeders;

use App\Models\Airplane;
use App\Models\AirplaneSit;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Airplane::factory([
            'sits_number' => 156
        ])->afterCreating(function ($airplane) {
            for ($y = 0; $y < $airplane->seat_rows; $y++) {
                for ($x = 0; $x < $airplane->seat_columns * 2; $x++) {
                    $side = $x >= $airplane->seat_columns ? 1 : 0;

                    AirplaneSit::factory([
                        'name' => chr(65 + $x).($y + 1),
                        'seat_side' => $side,
                        'row' => $y,
                        'column' => $x
                    ])->for($airplane)->create();
                }
            }
        })->create();
    }
}
