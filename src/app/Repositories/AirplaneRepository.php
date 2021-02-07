<?php

namespace App\Repositories;

use App\Models\Airplane;
use Illuminate\Support\Facades\DB;

class AirplaneRepository
{
    /**
     * Airplane Repository constructor.
     *
     * @param Airplane $airplaneModel
     */
    public function __construct(
        private Airplane $airplaneModel
    )
    {
        //
    }

    /**
     * Gets all registered airplanes.
     *
     * @param array|string[] $attributes
     * @return Airplane[]|\Illuminate\Database\Eloquent\Collection
     */
    public function all(array $attributes = Airplane::COLUMN_MAPPING): mixed
    {
        return $this->airplaneModel->all($attributes);
    }

    /**
     * Gets the airplane from ID.
     *
     * @param int            $airplaneId
     * @param array|string[] $attributes
     * @return mixed|Airplane
     */
    public function find(int $airplaneId, array $attributes = Airplane::COLUMN_MAPPING): mixed
    {
        return $this->airplaneModel->find($airplaneId, $attributes);
    }

    /**
     * Check if airplane exists.
     *
     * @param int $airplaneId
     * @return bool
     */
    public function checkExists(int $airplaneId): bool
    {
        return $this->airplaneModel->newQuery()
            ->where("id", $airplaneId)
            ->select(DB::raw(1))
            ->exists();
    }

    /**
     * Creates a new airplane.
     *
     * @param array $data
     * @return Airplane
     */
    public function create(array $data): Airplane
    {
        return $this->airplaneModel->create($data);
    }

    /**
     * Updates an airplane.
     *
     * @param array $data
     * @return bool
     */
    public function update(int $airplaneId, array $data): Airplane
    {
        $airplane = $this->airplaneModel->find($airplaneId, Airplane::COLUMN_MAPPING);
        $airplane->update($data);

        return $airplane;
    }
}
