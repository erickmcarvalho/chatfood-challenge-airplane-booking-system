<?php

namespace App\Repositories;

use App\Models\Passenger;

class PassengerRepository
{
    /**
     * Passenger Repository constructor.
     *
     * @param Passenger $passengerModel
     */
    public function __construct(
        private Passenger $passengerModel
    )
    {
        //
    }

    /**
     * Creates a new passenger.
     *
     * @param array $data
     * @return Passenger
     */
    public function create(array $data): Passenger
    {
        return $this->passengerModel->create($data);
    }
}
