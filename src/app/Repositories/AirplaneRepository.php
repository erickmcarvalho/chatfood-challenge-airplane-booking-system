<?php

namespace App\Repositories;

use App\Models\Airplane;

class AirplaneRepository
{
    /**
     * Airplane Repository constructor
     * .
     * @param Airplane $airplaneModel
     */
    public function __construct(
        private Airplane $airplaneModel
    )
    {
        //
    }

    /**
     * @param int            $airplaneId
     * @param array|string[] $attributes
     * @return mixed|Airplane
     */
    public function find(int $airplaneId, array $attributes = Airplane::COLUMN_MAPPING): mixed
    {
        return $this->airplaneModel->find($airplaneId, $attributes);
    }
}
