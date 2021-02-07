<?php

namespace Database\Seeders;

use App\Models\Airplane;
use App\Models\Flight;
use Illuminate\Database\Seeder;

class BookingTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $airplane = Airplane::factory([
            'seat_columns' => 3,
            'sits_number' => 156
        ])->createSits()->create();

        Flight::factory()->for($airplane)->create();
    }
}
