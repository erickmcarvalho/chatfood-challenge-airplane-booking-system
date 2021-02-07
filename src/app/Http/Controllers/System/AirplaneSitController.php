<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Http\Resources\AirplaneSitResource;
use App\Repositories\AirplaneSitRepository;

class AirplaneSitController extends Controller
{
    /**
     * Airplane Sit Controller constructor.
     *
     * @param AirplaneSitRepository $airplaneSitRepository
     */
    public function __construct(
        private AirplaneSitRepository $airplaneSitRepository
    )
    {
        //
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response|mixed
     */
    public function index(int $airplaneId)
    {
        return AirplaneSitResource::collection($this->airplaneSitRepository->getFromAirplane($airplaneId));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response|mixed
     */
    public function show(int $airplaneId, int $airplaneSitId)
    {
        $airplaneSit = $this->airplaneSitRepository->find($airplaneSitId, ['*']);

        if ($airplaneSit === null || $airplaneSit->airplane_id !== $airplaneId) {
            return $this->notFound([
                'code' => "notFound",
                'message' => "The airplane sit is not found."
            ]);
        }

        return new AirplaneSitResource($airplaneSit);
    }
}
