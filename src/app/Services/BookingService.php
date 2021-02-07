<?php

namespace App\Services;

use App\Exceptions\Services\BookingService\BookingServiceErrorException;
use App\Exceptions\Services\BookingService\LoadBookingException;
use App\Models\Airplane;
use App\Models\Flight;
use App\Models\FlightBooking;
use App\Models\FlightBookingSeat;
use App\Models\Passenger;
use App\Repositories\AirplaneRepository;
use App\Repositories\AirplaneSitRepository;
use App\Repositories\FlightBookingRepository;
use App\Repositories\FlightBookingSeatRepository;
use App\Repositories\FlightRepository;
use App\Repositories\PassengerRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class BookingService
{
    private Flight $flight;

    private Airplane $airplane;

    private $airplaneSits;

    /**
     * @var \Illuminate\Database\Eloquent\Collection|FlightBookingSeat[]
     */
    private $airplaneBookings;

    private ?FlightBooking $booking = null;

    /**
     * @var array|Collection|null
     */
    private $bookingSits = null;

    private ?Passenger $passenger = null;

    private array $matrix = [];

    private bool $loaded = false;

    public function __construct(
        private FlightRepository $flightRepository,
        private FlightBookingRepository $flightBookingRepository,
        private FlightBookingSeatRepository $flightBookingSeatRepository,
        private AirplaneRepository $airplaneRepository,
        private AirplaneSitRepository $airplaneSitRepository,
        private PassengerRepository $passengerRepository
    )
    {
        //
    }

    /**
     * Get and loads the flight.
     *
     * @param string $flightId
     * @throws LoadBookingException
     */
    public function load(string $flightId)
    {
        $flight = $this->flightRepository->find($flightId);

        if ($flight === null) {
            throw new LoadBookingException("The flight is not found.", LoadBookingException::FLIGHT_NOT_FOUND);
        }

        $this->flight = $flight;
        $this->airplane = $this->airplaneRepository->find($this->flight->airplane_id, [
            'sits_number',
            'seat_columns'
        ]);

        $this->airplaneSits = $this->airplaneSitRepository->getFromAirplane($this->flight->airplane_id);

        if ($this->airplaneSits->count() !== $this->airplane->sits_number) {
            throw new LoadBookingException("The airplane not has sits registers.", LoadBookingException::AIRPLANE_IS_INCOMPLETE);
        }

        $this->airplaneBookings = $this->flightBookingSeatRepository->allFromFlight($flightId);

        for ($i = 0; $i < 2; $i++) {
            $this->matrix[$i] = [];

            for ($y = 0; $y < $this->airplane->seat_rows / 2; $y++) {
                $this->matrix[$i][$y] = [];

                for ($x = 0; $x < $this->airplane->seat_columns; $x++) {
                    $this->matrix[$i][$y][$x] = [
                        'seat' => null,
                        'isFree' => null,
                        'side' => $i,
                        'row' => $y,
                        'column' => $x
                    ];
                }
            }
        }

        foreach ($this->airplaneSits as $airplaneSit) {
            $this->matrix[$airplaneSit->seat_side][$airplaneSit->row][$airplaneSit->column]['seat'] = $airplaneSit;
            $this->matrix[$airplaneSit->seat_side][$airplaneSit->row][$airplaneSit->column]['isFree'] = !$this->airplaneBookings->contains(function ($item) use ($airplaneSit) {
                return $item->airplane_sit_id === $airplaneSit->id;
            });
        }

        $this->booking = null;
        $this->bookingSits = null;
        $this->passenger = null;
        $this->loaded = true;
    }

    public function newBooking(): void
    {
        $this->booking = null;
        $this->bookingSits = null;
        $this->passenger = null;
    }

    public function reserveSeats(int $booking): bool
    {
        if ($this->loaded === false) {
            throw new BookingServiceErrorException("The service is not loaded.", BookingServiceErrorException::IS_NOT_LOADED);
        }

        $columns = 0; $rows = 0;
        $this->makeBookingMatrix($booking, $columns, $rows);

        $pending = $booking;

        for ($i = 0; $i < $this->airplane->seat_rows && $pending > 0; $i += $this->airplane->seat_columns) {
            // Left side
            $side = 0;
            $pending = $booking;
            $reserves = [];

            $start = $i / $this->airplane->seat_columns;

            for ($y = $start; $y < $rows + $start && $pending > 0; $y++) {
                for ($x = 0; $x < $columns && $pending > 0; $x++) {
                    if ($this->matrix[$side][$y][$x]['isFree'] === true) {
                        $reserves[] = &$this->matrix[$side][$y][$x];
                        --$pending;
                    }
                }
            }

            // Right side
            if ($pending > 0) {
                $side = 1;
                $pending = $booking;
                $reserves = [];

                for ($y = $start; $y < $rows + $start && $pending > 0; $y++) {
                    for ($x = 0; $x < $columns && $pending > 0; $x++) {
                        if ($this->matrix[$side][$y][2 - $x]['isFree'] === true) {
                            $reserves[] = &$this->matrix[$side][$y][2 - $x];
                            --$pending;
                        }
                    }
                }
            }

            // Both sides
            if ($pending > 0) {
                $pending = $booking;
                $reserves = [];

                for ($y = $start; $y < $rows && $pending > 0; $y++) {
                    for ($ix = 0; $ix < $columns * 2 && $pending > 0; $ix++) {
                        $side = intval($ix / $this->airplane->seat_columns);
                        $x = intval($ix % $this->airplane->seat_columns);

                        if ($this->matrix[$side][$y][$x]['isFree'] === true) {
                            $reserves[] = &$this->matrix[$side][$y][2 - $x];
                            --$pending;
                        }
                    }
                }
            }
        }

        // There are no seats available
        if ($pending > 0) {
            return false;
        }

        // Apply booking
        foreach ($reserves as &$reserve) {
            $reserve['isFree'] = false;
        }

        $this->bookingSits = collect(Arr::sort($reserves, function ($item) {
            return $item['side'] + $item['row'] + $item['column'];
        }));

        return true;
    }

    public function save(string $passengerName, string $passengerEmail)
    {
        if ($this->bookingSits === null) {
            throw new BookingServiceErrorException("Not has reserved seats.", BookingServiceErrorException::NOT_HAS_RESERVED_SEATS);
        }

        $this->passenger = $this->passengerRepository->create([
            'name' => $passengerName,
            'email' => $passengerEmail
        ]);

        $this->booking = $this->flightBookingRepository->create([
            'flight_id' => $this->flight->id,
            'passenger_id' => $this->passenger->id,
            'booking' => $this->bookingSits->count()
        ]);

        foreach ($this->bookingSits as $booking) {
            $this->flightBookingSeatRepository->create([
                'flight_booking_id' => $this->booking->id,
                'airplane_sit_id' => $booking['seat']->id
            ]);
        }
    }

    public function getAirplaneSits()
    {
        if ($this->loaded === false) {
            throw new BookingServiceErrorException("The service is not loaded.", BookingServiceErrorException::IS_NOT_LOADED);
        }

        return $this->airplaneSits;
    }

    public function getBooking()
    {
        if ($this->booking === null) {
            throw new BookingServiceErrorException("Not has booking registered.", BookingServiceErrorException::NOT_HAS_BOOKING);
        }

        return $this->booking;
    }

    public function getBookingSits()
    {
        if ($this->bookingSits === null) {
            throw new BookingServiceErrorException("Not has reserved seats.", BookingServiceErrorException::NOT_HAS_RESERVED_SEATS);
        }

        return $this->bookingSits;
    }

    public function getPassenger()
    {
        if ($this->booking === null) {
            throw new BookingServiceErrorException("Not has booking registered.", BookingServiceErrorException::NOT_HAS_BOOKING);
        }

        return $this->passenger;
    }

    private function makeBookingMatrix(int $booking, int &$columns, int &$rows): void {
        if ($booking % $this->airplane->seat_columns === 0) {
            $columns = $this->airplane->seat_columns;
            $rows = ($booking / $columns);
        } else {
            $columns = intval($booking / $this->airplane->seat_columns) + ($booking % $this->airplane->seat_columns);
            $rows = intval(ceil($booking / $columns));
        }
    }
}
