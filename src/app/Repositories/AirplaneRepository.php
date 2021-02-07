<?php

namespace App\Repositories;

use App\Models\Airplane;

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
     * Creates a new airplane.
     *
     * @param array $data
     * @return Airplane
     */
    public function create(array $data): Airplane
    {
        return $this->airplaneModel->create($data);
    }
}
