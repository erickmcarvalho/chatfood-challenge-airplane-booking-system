<?php

namespace Tests\Feature;

use App\Http\Resources\BookingFlightCollection;
use App\Http\Resources\BookingResource;
use App\Models\Flight;
use App\Services\BookingService;
use Database\Seeders\BookingTestSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Tests\TestCase;

class BookingApiTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private Flight $flight;

    public function setUp(): void
    {
        parent::setUp();

        $this->refreshDatabase();
        $this->seed(BookingTestSeeder::class);

        $this->flight = Flight::first();
    }

    private function mockPayload(array $replaces = [], array $removes = [])
    {
        return $this->makeMockData([
            'flightId' => $this->flight->id,
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'booking' => mt_rand(1, 7)
        ], $replaces, $removes);
    }

    public function test_get_available_flights()
    {
        // Expected response
        $expected = new BookingFlightCollection(collect([$this->flight])->forPage(1, 15));

        $response = $this->getJson(route("bookings.index")."?page=1");
        $response->assertSuccessful();
        $response->assertJson($expected->response()->getData(true));
    }

    public function test_create_booking_throws_validation_error()
    {
        // Missing fields
        $response = $this->postJson(route("bookings.store"), []);
        $response->assertJsonValidationErrors(['flightId', 'name', 'email', 'booking']);

        // Invalid format
        $response = $this->postJson(route("bookings.store"), $this->mockPayload([
            'flightId' => Str::random(),
            'email' => Str::random(),
            'booking' => Str::random()
        ]));
        $response->assertJsonValidationErrors(['flightId', 'email', 'booking']);

        // Invalid range min
        $response = $this->postJson(route("bookings.store"), $this->mockPayload([
            'booking' => 0
        ]));
        $response->assertJsonValidationErrors(['booking']);

        // Invalid range max
        $response = $this->postJson(route("bookings.store"), $this->mockPayload([
            'booking' => 10
        ]));
        $response->assertJsonValidationErrors(['booking']);
    }

    public function test_create_booking_throws_unavailable_seats_error()
    {
        // Populate data
        $bookingService = $this->app->get(BookingService::class);
        $bookingService->load($this->flight->id);

        for ($i = 0; $i < $bookingService->getAirplaneSits()->count(); $i++) {
            $bookingService->newBooking();
            $this->assertTrue($bookingService->reserveSeats(1));
            $bookingService->save($this->faker->name, $this->faker->email);
        }

        // Test
        $response = $this->postJson(route("bookings.store"), $this->mockPayload());
        //$response->dump();
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJson([
            'code' => "unavailableSeats"
        ]);
    }

    private function makeResponse(string $name, int $booking, array $seats)
    {
        return (new BookingResource([
            'passenger' => $name,
            'flight' => $this->flight,
            'booking' => $booking,
            'seats' => $seats
        ]))->response()->getData(true);
    }

    public function test_get_booking_info()
    {
        // Mock
        $data = [
            'flightId' => $this->flight->id,
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'booking' => mt_rand(1, 7)
        ];

        // Create
        $response = $this->postJson(route("bookings.store"), $data);
        $response->assertCreated();
        $responseData = json_decode($response->content(), true);

        // Test
        $response = $this->getJson(route("bookings.show", [$responseData['data']['id']]));
        $response->assertExactJson($responseData);
    }

    /**
     * Challenge example test 1
     *
     * Marco: 4 people;
     * Gerard: 2 people; Result:
     * Marco seats: 'A1', 'B1', 'A2', 'B2';
     * Gerard seats: 'E1', 'F1';
     */
    public function test_challenge_example_1()
    {
        // Marco
        $response = $this->postJson(route("bookings.store"), [
            'flightId' => $this->flight->id,
            'name' => "Marco",
            'email' => "marco@".$this->faker->freeEmailDomain,
            'booking' => 4
        ]);
        $response->assertCreated();
        $response->assertJson($this->makeResponse("Marco", 4, ['A1', 'B1', 'A2', 'B2']));

        // Gerard
        $response = $this->postJson(route("bookings.store"), [
            'flightId' => $this->flight->id,
            'name' => "Gerard",
            'email' => "gerard@".$this->faker->freeEmailDomain,
            'booking' => 2
        ]);
        $response->assertCreated();
        $response->assertJson($this->makeResponse("Gerard", 2, ['E1', 'F1']));
    }

    /**
     * Challenge example test 2
     *
     * Iosu: 2 people;
     * Oriol: 5 people;
     * David: 2 people; Result:
     * Iosu seats: 'A1', 'B1';
     * Oriol seats: 'D1', 'E1', 'F1', 'E2', 'F2';
     * David seats: 'A2', 'B2';
     */
    public function test_challenge_example_2()
    {
        // Marco
        $response = $this->postJson(route("bookings.store"), [
            'flightId' => $this->flight->id,
            'name' => "Iosu",
            'email' => "iosu@".$this->faker->freeEmailDomain,
            'booking' => 2
        ]);
        $response->assertCreated();
        $response->assertJson($this->makeResponse("Iosu", 2, ['A1', 'B1']));

        // Oriol
        $response = $this->postJson(route("bookings.store"), [
            'flightId' => $this->flight->id,
            'name' => "Oriol",
            'email' => "oriol@".$this->faker->freeEmailDomain,
            'booking' => 5
        ]);
        $response->assertCreated();
        $response->assertJson($this->makeResponse("Oriol", 5, ['D1', 'E1', 'F1', 'E2', 'F2']));

        // David
        $response = $this->postJson(route("bookings.store"), [
            'flightId' => $this->flight->id,
            'name' => "David",
            'email' => "david@".$this->faker->freeEmailDomain,
            'booking' => 2
        ]);
        $response->assertCreated();
        $response->assertJson($this->makeResponse("David", 2, ['A2', 'B2']));
    }

    /**
     * Challenge example test 3
     *
     * Iosu: 2 people;
     * Gerard: 2 people; Result:
     * Iosu seats: 'A1', 'B1';
     * Gerard seats: 'E1', 'F1';
     */
    public function test_challenge_example_3()
    {
        // Iosu
        $response = $this->postJson(route("bookings.store"), [
            'flightId' => $this->flight->id,
            'name' => "Iosu",
            'email' => "iosu@".$this->faker->freeEmailDomain,
            'booking' => 2
        ]);
        $response->assertCreated();
        $response->assertJson($this->makeResponse("Iosu", 2, ['A1', 'B1']));

        // Gerard
        $response = $this->postJson(route("bookings.store"), [
            'flightId' => $this->flight->id,
            'name' => "Gerard",
            'email' => "gerard@".$this->faker->freeEmailDomain,
            'booking' => 2
        ]);
        $response->assertCreated();
        $response->assertJson($this->makeResponse("Gerard", 2, ['E1', 'F1']));
    }
}
