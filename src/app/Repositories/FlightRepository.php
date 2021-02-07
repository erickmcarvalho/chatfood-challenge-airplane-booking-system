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
     * Gets all registered flights by pagination.
     *
     * @param array|string[] $attributes
     * @return Flight[]|\Illuminate\Database\Eloquent\Collection
     */
    public function all(int $perPage, array $attributes = Flight::COLUMN_MAPPING)
    {
        return $this->flightModel->simplePaginate($perPage, $attributes);
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
