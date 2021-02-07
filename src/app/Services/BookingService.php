<?php

namespace App\Services;

use App\Exceptions\Services\BookingService\BookingServiceErrorException;
use App\Exceptions\Services\BookingService\LoadBookingException;
use App\Models\Airplane;
use App\Models\AirplaneSit;
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
    /**
     * The flight resource.
     *
     * @var Flight
     */
    private Flight $flight;

    /**
     * The airplane resource.
     *
     * @var Airplane
     */
    private Airplane $airplane;

    /**
     * The airplane sits collection.
     *
     * @var \Illuminate\Database\Eloquent\Collection|AirplaneSit[]
     */
    private $airplaneSits;

    /**
     * The airplane seats that already reserved.
     *
     * @var \Illuminate\Database\Eloquent\Collection|FlightBookingSeat[]
     */
    private $airplaneBookings;

    /**
     * The booking resource.
     *
     * @var FlightBooking|null
     */
    private ?FlightBooking $booking = null;

    /**
     * The booking seats collection.
     *
     * @var array|Collection|null
     */
    private $bookingSits = null;

    /**
     * The booking passenger resource.
     *
     * @var Passenger|null
     */
    private ?Passenger $passenger = null;

    /**
     * The seats matrix.
     *
     * @var array
     */
    private array $matrix = [];

    /**
     * If the service is loaded.
     *
     * @var bool
     */
    private bool $loaded = false;

    /**
     * Booking Service constructor.
     *
     * @param FlightRepository            $flightRepository
     * @param FlightBookingRepository     $flightBookingRepository
     * @param FlightBookingSeatRepository $flightBookingSeatRepository
     * @param AirplaneRepository          $airplaneRepository
     * @param AirplaneSitRepository       $airplaneSitRepository
     * @param PassengerRepository         $passengerRepository
     */
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

    /**
     * Start new booking.
     *
     * @returns void
     */
    public function newBooking(): void
    {
        $this->booking = null;
        $this->bookingSits = null;
        $this->passenger = null;
    }

    /**
     * Try reserve seats.
     * If no available seats are found, returns "false".
     *
     * @param int $booking
     * @return bool
     * @throws BookingServiceErrorException
     */
    public function reserveSeats(int $booking): bool
    {
        if ($this->loaded === false) {
            throw new BookingServiceErrorException("The service is not loaded.", BookingServiceErrorException::IS_NOT_LOADED);
        }

        $pending = $booking;

        if ($pending > 1) {
            $columns = 0; $rows = 0;
            $this->makeBookingMatrix($booking, $columns, $rows);

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
        } else {
            for ($y = 0; $y < $this->airplane->seat_rows && $pending > 0; $y++) {
                for ($x = 0; $x < $this->airplane->seat_columns && $pending > 0; $x++) {
                    $side = 0;
                    $tx = $x;

                    if ($this->matrix[$side][$y][$x]['isFree'] === true || $this->matrix[++$side][$y][($tx = 2 - $x)]['isFree'] === true) {
                        $reserves[] = &$this->matrix[$side][$y][$tx];
                        --$pending;
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

    /**
     * Save the booking in database.
     *
     * @param string $passengerName
     * @param string $passengerEmail
     * @returns void
     * @throws BookingServiceErrorException
     */
    public function save(string $passengerName, string $passengerEmail): void
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

    /**
     * Gets the flight.
     *
     * @return Flight
     * @throws BookingServiceErrorException
     */
    public function getFlight(): Flight
    {
        if ($this->loaded === false) {
            throw new BookingServiceErrorException("The service is not loaded.", BookingServiceErrorException::IS_NOT_LOADED);
        }

        return $this->flight;
    }

    /**
     * Gets the all airplane sits.
     *
     * @return AirplaneSit[]|\Illuminate\Database\Eloquent\Collection
     * @throws BookingServiceErrorException
     */
    public function getAirplaneSits(): mixed
    {
        if ($this->loaded === false) {
            throw new BookingServiceErrorException("The service is not loaded.", BookingServiceErrorException::IS_NOT_LOADED);
        }

        return $this->airplaneSits;
    }

    /**
     * Gets the booking resource.
     *
     * @return FlightBooking
     * @throws BookingServiceErrorException
     */
    public function getBooking(): FlightBooking
    {
        if ($this->booking === null) {
            throw new BookingServiceErrorException("Not has booking registered.", BookingServiceErrorException::NOT_HAS_BOOKING);
        }

        return $this->booking;
    }

    /**
     * Gets the booking sits.
     *
     * @return array|Collection|null
     * @throws BookingServiceErrorException
     */
    public function getBookingSits()
    {
        if ($this->bookingSits === null) {
            throw new BookingServiceErrorException("Not has reserved seats.", BookingServiceErrorException::NOT_HAS_RESERVED_SEATS);
        }

        return $this->bookingSits;
    }

    /**
     * Get the passenger resource.
     *
     * @return Passenger
     * @throws BookingServiceErrorException
     */
    public function getPassenger(): Passenger
    {
        if ($this->booking === null) {
            throw new BookingServiceErrorException("Not has booking registered.", BookingServiceErrorException::NOT_HAS_BOOKING);
        }

        return $this->passenger;
    }

    /**
     * Gets the info of a specific booking.
     *
     * @param string $flightBookingId
     * @return array|null
     */
    public function getBookingInfo(string $flightBookingId): ?array
    {
        $flightBooking = $this->flightBookingRepository->find($flightBookingId);

        if ($flightBooking === null) {
            return null;
        }

        $flightBookingSeats = $flightBooking->seats;
        $airplaneSits = $this->airplaneSitRepository->getFromAirplane($flightBooking->flight->airplane_id);
        $bookingSits = collect([]);

        foreach ($flightBookingSeats as $flightBookingSeat) {
            $airplaneSit = $airplaneSits->firstWhere('id', $flightBookingSeat->airplane_sit_id);

            $bookingSits->push([
                'seat' => $airplaneSit,
                'side' => $airplaneSit->seat_side,
                'row' => $airplaneSit->row,
                'column' => $airplaneSit->column
            ]);
        }

        return [
            'id' => $flightBookingId,
            'passenger' => $flightBooking->passenger,
            'flight' => $flightBooking->flight,
            'booking' => $flightBookingSeats->count(),
            'seats' => $bookingSits
        ];
    }

    /**
     * Makes the matrix by booking number.
     *
     * @param int $booking
     * @param int $columns
     * @param int $rows
     * @returns void
     */
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
