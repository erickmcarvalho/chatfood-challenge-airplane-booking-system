<?php

namespace App\Exceptions\Services\BookingService;

use Exception;

class LoadBookingException extends Exception
{
    /**
     * The flight is not found.
     */
    public const FLIGHT_NOT_FOUND = 0x01;

    /**
     * The airplane register is incomplete.
     */
    public const AIRPLANE_IS_INCOMPLETE = 0x02;
}
