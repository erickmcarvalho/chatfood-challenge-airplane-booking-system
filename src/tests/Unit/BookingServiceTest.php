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

class BookingServiceTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private BookingService $bookingService;

    private Flight $flight;

    public function setUp(): void
    {
        parent::setUp();

        $this->bookingService = $this->app->get(BookingService::class);
        $this->refresh();
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
}
