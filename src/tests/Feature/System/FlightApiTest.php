<?php

namespace Tests\Feature\System;

use App\Http\Resources\FlightResource;
use App\Models\Airplane;
use App\Models\Flight;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FlightApiTest extends TestCase
{
    use RefreshDatabase;

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
}
