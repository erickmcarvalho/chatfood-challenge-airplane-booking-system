<?php

namespace Tests\Unit;

use App\Exceptions\Services\BookingService\LoadBookingException;
use App\Models\Airplane;
use App\Models\Flight;
use App\Services\BookingService;
use Database\Seeders\BookingTestSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BookingServiceTest extends TestCase
{
    use RefreshDatabase;

    private BookingService $bookingService;

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
    }

    public function test_throws_load_exception()
    {
        // The flight is not found.
        $this->expectException(LoadBookingException::class);
        $this->expectExceptionCode(LoadBookingException::FLIGHT_NOT_FOUND);
        $this->bookingService->load(Str::uuid());

        // The airplane register is incomplete.
        $airplane = Airplane::factory()->create();
        $flight = Flight::factory(['airplane_id' => $airplane->id])->create();

        $this->expectException(LoadBookingException::class);
        $this->expectExceptionCode(LoadBookingException::AIRPLANE_IS_INCOMPLETE);
        $this->bookingService->load($flight->id);
    }
}
