<?php

namespace Database\Factories;

use App\Models\Airplane;
use App\Models\AirplaneSit;
use Illuminate\Database\Eloquent\Factories\Factory;

class AirplaneSitFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AirplaneSit::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'airplane_id' => Airplane::factory()
        ];
    }
}
