<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Http\Requests\System\Airplane\CreateAirplaneRequest;
use App\Http\Resources\AirplaneResource;
use App\Repositories\AirplaneRepository;
use App\Services\AirplaneService;
use Illuminate\Http\Request;

class AirplaneController extends Controller
{
    public function __construct(
        private AirplaneRepository $airplaneRepository,
        private AirplaneService $airplaneService
    )
    {
        //
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response|mixed
     */
    public function index()
    {
        return AirplaneResource::collection($this->airplaneRepository->all(['*']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateAirplaneRequest $request)
    {
        $airplane = $this->airplaneService->createAirplane(
            name: $request->input("name"),
            company: $request->input("company"),
            seatColumns: intval($request->input("seatColumns")),
            sitsNumber: intval($request->input("sitsNumber"))
        );

        return $this->created(new AirplaneResource($airplane));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
