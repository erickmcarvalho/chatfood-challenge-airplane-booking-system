<?php

namespace Database\Factories;

use App\Models\Airplane;
use App\Models\AirplaneSit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

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
            'name' => $this->faker->firstNameFemale,
            'company' => $this->faker->company,
            'sits_number' => Arr::random([60, 120, 156]),
            'seat_columns' => 3
        ];
    }

    /**
     * Define the creating sits event.
     *
     * @return AirplaneFactory
     */
    public function createSits()
    {
        return $this->afterCreating(function ($airplane) {
            for ($y = 0; $y < $airplane->seat_rows / 2; $y++) {
                for ($x = 0; $x < $airplane->seat_columns * 2; $x++) {
                    $side = $x >= $airplane->seat_columns ? 1 : 0;

                    AirplaneSit::factory([
                        'name' => chr(65 + $x).($y + 1),
                        'seat_side' => $side,
                        'row' => $y,
                        'column' => $side === 1 ? ($airplane->seat_columns - $x) * -1 : $x,
                    ])->for($airplane)->create();
                }
            }
        });
    }
}
