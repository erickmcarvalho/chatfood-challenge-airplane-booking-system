<?php

namespace Tests\Feature\System;

use App\Models\Airplane;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\TestCase;

class AirplaneApiTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_create_airplanes_throws_validation_errors()
    {
        // Missing fields
        $response = $this->postJson(route("system.airplanes.store", []));
        $response->assertJsonValidationErrors(['name', 'company', 'seatColumns', 'sitsNumber']);

        // Invalid format
        $response = $this->postJson(route("system.airplanes.store"), [
            'name' => $this->faker->firstNameFemale,
            'company' => $this->faker->company,
            'seatColumns' => Str::random(),
            'sitsNumber' => Str::random()
        ]);
        $response->assertJsonValidationErrors(['seatColumns', 'sitsNumber']);

        // Conflict sits number with seat columns
        $response = $this->postJson(route("system.airplanes.store"), [
            'name' => $this->faker->firstNameFemale,
            'company' => $this->faker->company,
            'seatColumns' => 3,
            'sitsNumber' => 100
        ]);
        $response->assertJsonValidationErrors(['sitsNumber']);
    }

    public function test_create_airplanes_success()
    {
        // Data
        $data = [
            'name' => $this->faker->firstNameFemale,
            'company' => $this->faker->company,
            'seatColumns' => 3,
            'sitsNumber' => 156
        ];

        // Test
        $response = $this->postJson(route("system.airplanes.store"), $data);
        $response->assertCreated();
        $response->assertJson($data);

        $this->assertDatabaseHas("airplane_sits", [
            "airplane_id" => $response['id']
        ]);
    }

    public function test_get_all_airplanes()
    {
        // Refresh
        $this->refreshDatabase();

        // Mocks
        $airplanes = Airplane::factory()->count(5)->create();

        // Test response
        $expected = [
            'data' => []
        ];

        foreach ($airplanes as $airplane) {
            $expected['data'][] = [
                'id' => $airplane->id,
                'name' => $airplane->name,
                'company' => $airplane->company,
                'sitsNumber' => $airplane->sits_number,
                'seatColumns' => $airplane->seat_columns
            ];
        }

        // Tests
        $response = $this->getJson(route("system.airplanes.index"));
        $response->assertJson($expected);
    }
}
