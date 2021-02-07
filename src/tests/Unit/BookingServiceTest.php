<?php

namespace Tests\Unit;

use App\Exceptions\Services\BookingService\BookingServiceErrorException;
use App\Exceptions\Services\BookingService\LoadBookingException;
use App\Models\Airplane;
use App\Models\Flight;
use App\Services\BookingService;
use Database\Seeders\BookingTestSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\TestCase;
use ReflectionClass;
use ReflectionProperty;

class BookingServiceTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private BookingService $bookingService;

    private ReflectionProperty $bookingServiceMatrixReflection;

    private Flight $flight;

    public function setUp(): void
    {
        parent::setUp();

        // Injection booking service
        $this->bookingService = $this->app->get(BookingService::class);

        // Get private $matrix property to test
        $reflection = new ReflectionClass($this->bookingService);
        $this->bookingServiceMatrixReflection = $reflection->getProperty('matrix');
        $this->bookingServiceMatrixReflection->setAccessible(true);

        // Refresh database
        $this->refresh();
    }

    private function getMatrix()
    {
        return $this->bookingServiceMatrixReflection->getValue($this->bookingService);
    }

    private function refresh()
    {
        $this->refreshDatabase();
        $this->seed(BookingTestSeeder::class);

        $this->flight = Flight::first();
    }

    public function test_load_throws_flight_not_found_exception()
    {
        // The flight is not found.
        $this->expectException(LoadBookingException::class);
        $this->expectExceptionCode(LoadBookingException::FLIGHT_NOT_FOUND);
        $this->bookingService->load(Str::uuid());
    }

    public function test_load_throws_airplane_is_incomplete_exception()
    {
        // The airplane register is incomplete.
        $airplane = Airplane::factory()->create();
        $flight = Flight::factory(['airplane_id' => $airplane->id])->create();

        $this->expectException(LoadBookingException::class);
        $this->expectExceptionCode(LoadBookingException::AIRPLANE_IS_INCOMPLETE);
        $this->bookingService->load($flight->id);
    }

    public function test_throws_is_not_loaded_exception()
    {
        // The service is not loaded.
        $this->expectException(BookingServiceErrorException::class);
        $this->expectExceptionCode(BookingServiceErrorException::IS_NOT_LOADED);
        $this->bookingService->reserveSeats(5);
    }

    public function test_save_method_throws_not_has_booking_exception()
    {
        $this->bookingService->load($this->flight->id);

        // Not has reserved seats.
        $this->expectException(BookingServiceErrorException::class);
        $this->expectExceptionCode(BookingServiceErrorException::NOT_HAS_RESERVED_SEATS);
        $this->bookingService->save($this->faker->name, $this->faker->email);
    }

    public function test_get_airplane_sits_method_throws_is_not_loaded_exception()
    {
        // The service is not loaded.
        $this->expectException(BookingServiceErrorException::class);
        $this->expectExceptionCode(BookingServiceErrorException::IS_NOT_LOADED);
        $this->bookingService->getAirplaneSits();
    }

    public function test_get_bookings_sits_method_throws_not_has_booking_exception()
    {
        $this->bookingService->load($this->flight->id);

        // Not has reserved seats.
        $this->expectException(BookingServiceErrorException::class);
        $this->expectExceptionCode(BookingServiceErrorException::NOT_HAS_RESERVED_SEATS);
        $this->bookingService->getBookingSits();
    }

    public function test_get_passenger_method_throws_not_has_booking_exception()
    {
        $this->expectException(BookingServiceErrorException::class);
        $this->expectExceptionCode(BookingServiceErrorException::NOT_HAS_BOOKING);
        $this->bookingService->getPassenger();
    }

    public function test_get_booking_method_throws_not_has_booking_exception()
    {
        $this->expectException(BookingServiceErrorException::class);
        $this->expectExceptionCode(BookingServiceErrorException::NOT_HAS_BOOKING);
        $this->bookingService->getBooking();
    }

    public function test_there_are_no_seats_available()
    {
        $this->bookingService->load($this->flight->id);

        for ($i = 0; $i < $this->bookingService->getAirplaneSits()->count(); $i++) {
            $this->assertTrue($this->bookingService->reserveSeats(1));
        }

        $this->assertFalse($this->bookingService->reserveSeats(2));
    }

    private function assertSeatsMatrixFree(array $matrix, array $skips)
    {
        $skipCollect = collect($skips);

        // Test others are free
        foreach ($this->bookingService->getAirplaneSits() as $airplaneSit) {
            // Skip
            if ($skipCollect->contains(function ($item) use ($airplaneSit) {
                return $airplaneSit->seat_side === $item[0] && $airplaneSit->row === $item[1] && $airplaneSit->column === $item[2];
            })) {
                continue;
            }

            $this->assertTrue($matrix[$airplaneSit->seat_side][$airplaneSit->row][$airplaneSit->column]['isFree']);
        }
    }

    public function test_booking_save_success()
    {
        $this->bookingService->load($this->flight->id);

        // Test matrix reserve
        $this->assertTrue($this->bookingService->reserveSeats(4));
        $this->assertEquals("A1,B1,A2,B2", $this->bookingService->getBookingSits()->pluck("seat.name")->implode(","));

        // Test save data
        $this->bookingService->save($this->faker->name, $this->faker->email);

        $this->assertDatabaseHas("passengers", [
            'id' => $this->bookingService->getPassenger()->id
        ]);
        $this->assertDatabaseHas("flight_bookings", [
            'id' => $this->bookingService->getBooking()->id,
            'flight_id' => $this->flight->id
        ]);
        $this->assertDatabaseHas("flight_booking_seats", [
            'flight_booking_id' => $this->bookingService->getBooking()->id
        ]);

        // Test matrix reload
        $this->bookingService->load($this->flight->id);

        // Test matrix
        $matrix = $this->getMatrix();

        // Test A1,B1,A2,B2 is busy
        $this->assertFalse($matrix[0][0][0]['isFree']); // A1
        $this->assertFalse($matrix[0][0][1]['isFree']); // B1
        $this->assertFalse($matrix[0][1][0]['isFree']); // A2
        $this->assertFalse($matrix[0][1][1]['isFree']); // B2

        // Test others are free
        $this->assertSeatsMatrixFree($matrix, [
            [0, 0, 0], // A1
            [0, 0, 1], // B1
            [0, 1, 0], // A2
            [0, 1, 1]  // B2
        ]);
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
        $this->bookingService->load($this->flight->id);

        // Marco booking
        $this->assertTrue($this->bookingService->reserveSeats(4));

        $marco = $this->bookingService->getBookingSits();

        $matrix = $this->getMatrix();
        $this->assertFalse($matrix[0][0][0]['isFree']); // A1
        $this->assertFalse($matrix[0][0][1]['isFree']); // B1
        $this->assertFalse($matrix[0][1][0]['isFree']); // A2
        $this->assertFalse($matrix[0][1][1]['isFree']); // B2

        // Gerard booking
        $this->bookingService->newBooking();
        $this->assertTrue($this->bookingService->reserveSeats(2));

        $gerard = $this->bookingService->getBookingSits();

        $matrix = $this->getMatrix();
        $this->assertFalse($matrix[1][0][1]['isFree']); // E1
        $this->assertFalse($matrix[1][0][2]['isFree']); // F1

        // Assert contents
        $this->assertEquals("A1,B1,A2,B2", $marco->pluck("seat.name")->implode(","));
        $this->assertEquals("E1,F1", $gerard->pluck("seat.name")->implode(","));

        // Assert matrix
        $matrix = $this->getMatrix();
        $this->assertFalse($matrix[0][0][0]['isFree']); // A1
        $this->assertFalse($matrix[0][0][1]['isFree']); // B1
        $this->assertFalse($matrix[0][1][0]['isFree']); // A2
        $this->assertFalse($matrix[0][1][1]['isFree']); // B2
        $this->assertFalse($matrix[1][0][1]['isFree']); // E1
        $this->assertFalse($matrix[1][0][2]['isFree']); // F1

        // Test others are free
        $this->assertSeatsMatrixFree($matrix, [
            [0, 0, 0], // A1
            [0, 0, 1], // B1
            [0, 1, 0], // A2
            [0, 1, 1], // B2
            [1, 0, 1], // E1
            [1, 0, 2]  // F1
        ]);
    }
}
