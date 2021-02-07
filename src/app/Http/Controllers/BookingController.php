<?php

namespace App\Http\Controllers;

use App\Exceptions\Services\BookingService\LoadBookingException;
use App\Http\Requests\Booking\CreateBookingRequest;
use App\Http\Resources\BookingFlightCollection;
use App\Http\Resources\BookingResource;
use App\Repositories\FlightRepository;
use App\Services\BookingService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(
        private BookingService $bookingService,
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
        return new BookingFlightCollection($this->flightRepository->all(15));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|mixed
     */
    public function store(CreateBookingRequest $request)
    {
        try {
            $this->bookingService->load($request->input("flightId"));
        } catch (LoadBookingException $exception) {
            if ($exception->getCode() === LoadBookingException::FLIGHT_NOT_FOUND) {
                return $this->notFound([
                    'code' => "flightNotFound",
                    'message' => "The flight is not found."
                ]);
            }

            return $this->notFound([
                'code' => "unavailableService",
                'message' => "Unavailable service."
            ]);
        }

        if ($this->bookingService->reserveSeats($request->input("booking")) === false) {
            return $this->badRequest([
                'code' => "unavailableSeats",
                'message' => "There are no seats available for booking."
            ]);
        }

        $this->bookingService->save(
            passengerName: $request->input("name"),
            passengerEmail: $request->input("email")
        );

        return $this->created(new BookingResource([
            'id' => $this->bookingService->getBooking()->id,
            'passenger' => $this->bookingService->getPassenger()->name,
            'flight' => $this->bookingService->getFlight(),
            'booking' => intval($request->input("booking")),
            'seats' => $this->bookingService->getBookingSits()
        ]));
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
