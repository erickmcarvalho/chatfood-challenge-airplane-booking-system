<?php

namespace App\Services;

use App\Models\Airplane;
use App\Repositories\AirplaneRepository;
use App\Repositories\AirplaneSitRepository;

class AirplaneService
{
    /**
     * Airplane Service constructor.
     *
     * @param AirplaneRepository    $airplaneRepository
     * @param AirplaneSitRepository $airplaneSitRepository
     */
    public function __construct(
        private AirplaneRepository $airplaneRepository,
        private AirplaneSitRepository $airplaneSitRepository
    )
    {
        //
    }

    /**
     * Create a new airplane with all sits.
     *
     * @param string $name
     * @param string $company
     * @param int    $seatColumns
     * @param int    $sitsNumber
     * @return Airplane
     */
    public function createAirplane(string $name, string $company, int $seatColumns, int $sitsNumber): Airplane
    {
        $airplane = $this->airplaneRepository->create([
            'name' => $name,
            'company' => $company,
            'seat_columns' => $seatColumns,
            'sits_number' => $sitsNumber
        ]);

        for ($y = 0; $y < $airplane->seat_rows; $y++) {
            for ($x = 0; $x < $seatColumns * 2; $x++) {
                $side = $x >= $seatColumns ? 1 : 0;

                $this->airplaneSitRepository->create([
                    'airplane_id' => $airplane->id,
                    'name' => chr(65 + $x).($y + 1),
                    'seat_side' => $side,
                    'row' => $y,
                    'column' => $x
                ]);
            }
        }

        return $airplane;
    }
}
