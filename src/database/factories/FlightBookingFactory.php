<?php

namespace Database\Factories;

use App\Models\Flight;
use App\Models\FlightBooking;
use App\Models\Passenger;
use Illuminate\Database\Eloquent\Factories\Factory;

class FlightBookingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FlightBooking::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "flight_id" => Flight::factory(),
            "passenger_id" => Passenger::factory(),
            "booking" => mt_rand(1, 7)
        ];
    }
}
