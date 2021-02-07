<?php

namespace App\Repositories;

use App\Models\FlightBookingSeat;

class FlightBookingSeatRepository
{
    /**
     * Flight Booking Seat Repository constructor.
     *
     * @param FlightBookingSeat $flightBookingSeatModel
     */
    public function __construct(
        private FlightBookingSeat $flightBookingSeatModel
    )
    {
        //
    }

    /**
     * Get all seat bookings from a flight.
     *
     * @param string $flightId
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function allFromFlight(string $flightId)
    {
        return $this->flightBookingSeatModel->newQuery()
            ->join("flight_bookings", "flight_bookings.id", "=", "flight_booking_seats.flight_booking_id")
            ->get(['flight_booking_seats.*']);
    }

    /**
     * Get sits from a flight booking.
     *
     * @param string         $flightBookingId
     * @param array|string[] $attributes
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getFromFlightBooking(string $flightBookingId, array $attributes = FlightBookingSeat::COLUMN_MAPPING)
    {
        return $this->flightBookingSeatModel->newQuery()
            ->where("flight_booking_id", $flightBookingId)
            ->get($attributes);
    }

    /**
     * Creates a new flight booking seat register.
     *
     * @param array $data
     * @return FlightBookingSeat
     */
    public function create(array $data): FlightBookingSeat
    {
        return $this->flightBookingSeatModel->create($data);
    }
}
