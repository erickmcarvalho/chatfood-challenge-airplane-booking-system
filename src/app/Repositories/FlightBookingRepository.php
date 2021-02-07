<?php

namespace App\Repositories;

use App\Models\FlightBooking;

class FlightBookingRepository
{
    /**
     * Flight Booking Repository constructor.
     *
     * @param FlightBooking $flightBookingModel
     */
    public function __construct(
        private FlightBooking $flightBookingModel
    )
    {
        //
    }

    /**
     * Gets a flight booking by ID.
     *
     * @param string         $flightBookingId
     * @param array|string[] $attributes
     * @return mixed|FlightBooking
     */
    public function find(string $flightBookingId, array $attributes = FlightBooking::COLUMN_MAPPING): ?FlightBooking
    {
        return $this->flightBookingModel->find($flightBookingId, $attributes);
    }

    /**
     * Creates a new flight booking.
     *
     * @param array $data
     * @return FlightBooking
     */
    public function create(array $data): FlightBooking
    {
        return $this->flightBookingModel->create($data);
    }
}
