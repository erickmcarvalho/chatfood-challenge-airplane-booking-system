<?php

namespace Tests\Feature\System;

use App\Http\Resources\FlightResource;
use App\Models\Airplane;
use App\Models\Flight;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\TestCase;

class FlightApiTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    private function mockFlight(array $replaces = [], array $removes = [])
    {
        $source = $this->makeFaker();
        $destination = $this->makeFaker();

        return $this->makeMockData([
            'name' => "Flight from ".$source->city." (".$source->country.") to ".$destination->city." (".$destination->country.")",
            'source' => $source->city."/".$source->currencyCode,
            'destination' => $destination->city."/".$destination->countryCode,
            'flightDate' => $this->faker->dateTime->format("Y-m-d\TH:i:sP"),
            'airplaneId' => Airplane::factory()->create()->id
        ], $replaces, $removes);
    }

    public function test_get_all_flights_page_1()
    {
        // Mock
        $flights = Flight::factory()->count(100)->create();

        // Response
        $expected = $flights->take(15);
        $resource = FlightResource::collection($expected);

        // Tests
        $response = $this->getJson(route("system.flights.index")."?page=1");
        $response->assertJson($resource->response()->getData(true));
    }

    public function test_get_all_flights_page_2()
    {
        // Mock
        $flights = Flight::factory()->count(100)->create();

        // Response
        $expected = $flights->skip(15 * 1)->take(15);
        $resource = FlightResource::collection($expected);

        // Tests
        $response = $this->getJson(route("system.flights.index")."?page=2");
        $response->assertJson($resource->response()->getData(true));
    }

    public function test_get_all_flights_page_3()
    {
        // Mock
        $flights = Flight::factory()->count(100)->create();

        // Response
        $expected = $flights->skip(15 * 2)->take(15);
        $resource = FlightResource::collection($expected);

        // Tests
        $response = $this->getJson(route("system.flights.index")."?page=3");
        $response->assertJson($resource->response()->getData(true));
    }

    public function test_create_flight_throws_validation_errors()
    {
        // Missing fields
        $response = $this->postJson(route("system.flights.store"), []);
        $response->assertJsonValidationErrors(['name', 'source', 'destination', 'flightDate', 'airplaneId']);

        // Invalid format
        $response = $this->postJson(route("system.flights.store"), $this->mockFlight([
            'flightDate' => Str::random(),
            'airplane_id' => Str::random()
        ]));
        $response->assertJsonValidationErrors(['flightDate', 'airplaneId']);

        // Not found
        $response = $this->postJson(route("system.flights.store"), $this->mockFlight([
            'flightDate' => Str::random(),
            'airplane_id' => mt_rand(10000, 99999)
        ]));
        $response->assertJsonValidationErrors(['airplaneId']);
    }

    public function test_create_flight_success()
    {
        // Mock
        $data = $this->mockFlight();

        // Test
        $response = $this->postJson(route("system.flights.store"), $data);
        //$response->dump();
        $response->assertCreated();
        $response->assertJson($data);

        $this->assertDatabaseHas("flights", [
            "id" => $response['id']
        ]);
    }

    public function test_get_specific_flight()
    {
        // Mock
        $flight = Flight::factory()->create();

        // Response
        $resource = new FlightResource($flight);
        $resource->withAirplaneId();
        $expected = $resource->response()->getData(true);

        // Test
        $response = $this->getJson(route("system.flights.show", [$flight->id]));
        $response->assertJson($expected);
    }
}
