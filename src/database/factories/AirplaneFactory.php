<?php

namespace Database\Factories;

use App\Models\Airplane;
use Illuminate\Database\Eloquent\Factories\Factory;

class AirplaneFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Airplane::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->titleMale,
            'company' => $this->faker->company,
            'sits_number' => array_rand([60, 100, 156]),
            'seat_sides' => array_rand([2, 3])
        ];
    }
}
