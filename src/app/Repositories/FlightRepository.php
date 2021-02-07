<?php

namespace App\Repositories;

use App\Models\Flight;
use Illuminate\Support\Facades\DB;

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

    /**
     * Check if flight exists.
     *
     * @param string $flightId
     * @return bool
     */
    public function checkExists(string $flightId): bool
    {
        return $this->flightModel->newQuery()
            ->where("id", $flightId)
            ->select(DB::raw(1))
            ->exists();
    }

    /**
     * Creates a new flight register.
     *
     * @param array $data
     * @return Flight
     */
    public function create(array $data): Flight
    {
        return $this->flightModel->create($data);
    }

    /**
     * Updates a flight register.
     *
     * @param string $flightId
     * @param array  $data
     * @return Flight
     */
    public function update(string $flightId, array $data): Flight
    {
        $flight = $this->flightModel->find($flightId, Flight::COLUMN_MAPPING);
        $flight->update($data);

        return $flight;
    }

    /**
     * Delete an flight from database.
     *
     * @param string $flightId
     * @return bool
     */
    public function delete(string $flightId): bool
    {
        return $this->flightModel->newQuery()
            ->where("id", $flightId)
            ->select(DB::raw(1))
            ->delete() > 0;
    }
}
