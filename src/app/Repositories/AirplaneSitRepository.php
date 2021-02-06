<?php

namespace App\Repositories;

use App\Models\AirplaneSit;

class AirplaneSitRepository
{
    /**
     * Airplane Sit Repository constructor.
     *
     * @param AirplaneSit $airplaneSitModel
     */
    public function __construct(
        private AirplaneSit $airplaneSitModel
    )
    {
        //
    }

    /**
     * Gets the sits of an airplane.
     *
     * @param int            $airplaneId
     * @param array|string[] $attributes
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getFromAirplane(int $airplaneId, array $attributes = AirplaneSit::COLUMN_MAPPING)
    {
        return $this->airplaneSitModel->newQuery()
            ->where("airplane_id", $airplaneId)
            ->get($attributes);
    }
}
