<?php

namespace Database\Factories;

use App\Models\AirplaneSit;
use App\Models\FlightBookingSeat;
use Illuminate\Database\Eloquent\Factories\Factory;

class FlightBookingSeatFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FlightBookingSeat::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "flight_booking_id" => FlightBookingSeat::factory(),
            "airplane_sit_id" => AirplaneSit::factory()
        ];
    }
}
