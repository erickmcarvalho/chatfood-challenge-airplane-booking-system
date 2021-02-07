<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Http\Requests\System\Flight\CreateFlightRequest;
use App\Http\Resources\FlightResource;
use App\Repositories\FlightRepository;
use Illuminate\Http\Request;

class FlightController extends Controller
{
    /**
     * Flight Controller constructor.
     *
     * @param FlightRepository $flightRepository
     */
    public function __construct(
        private FlightRepository $flightRepository
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
        $flights = $this->flightRepository->all(15);

        return FlightResource::collection($flights);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|mixed
     */
    public function store(CreateFlightRequest $request)
    {
        $resource = new FlightResource($this->flightRepository->create([
            "name" => $request->input("name"),
            "source" => $request->input("source"),
            "destination" => $request->input("destination"),
            "flight_date" => $request->input("flightDate"),
            "airplane_id" => intval($request->input("airplaneId"))
        ]));

        $resource->withAirplaneId();

        return $this->created($resource);
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
