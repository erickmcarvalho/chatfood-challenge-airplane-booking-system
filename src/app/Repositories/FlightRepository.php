<?php

namespace App\Repositories;

use App\Models\Flight;

class FlightRepository
{
    /**
     * Flight Repository constructor.
     *
     * @param Flight $flightModel
     */
    public function __construct(
        private Flight $flightModel
    )
    {
        //
    }

    /**
     * Find a flight by ID.
     *
     * @param string         $flightId
     * @param array|string[] $attributes
     * @return mixed
     */
    public function find(string $flightId, array $attributes = Flight::COLUMN_MAPPING)
    {
        return $this->flightModel->find($flightId, $attributes);
    }
}
