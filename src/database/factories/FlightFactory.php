<?php

namespace Database\Factories;

use App\Models\Airplane;
use App\Models\Flight;
use Illuminate\Database\Eloquent\Factories\Factory;

class FlightFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Flight::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $source = $this->withFaker();
        $destination = $this->withFaker();

        return [
            'name' => "Flight from ".$source->city." (".$source->country.") to ".$destination->city." (".$destination->country.")",
            'source' => $source->city."/".$source->currencyCode,
            'destination' => $destination->city."/".$destination->countryCode,
            'flight_date' => $this->faker->dateTimeThisCentury("+10 days"),
            'airplane_id' => Airplane::factory()
        ];
    }
}
