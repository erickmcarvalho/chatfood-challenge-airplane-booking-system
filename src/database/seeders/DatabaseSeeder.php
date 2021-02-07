<?php

namespace Database\Seeders;

use App\Models\Airplane;
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
        ])->createSits()->create();
    }
}
