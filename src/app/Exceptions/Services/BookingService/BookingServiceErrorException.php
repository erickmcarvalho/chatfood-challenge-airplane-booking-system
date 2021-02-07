<?php

namespace App\Exceptions\Services\BookingService;

use Exception;

class BookingServiceErrorException extends Exception
{
    /**
     * The service is not loaded.
     */
    public const IS_NOT_LOADED = 0x01;

    /**
     * Not has reserved seats.
     */
    public const NOT_HAS_RESERVED_SEATS = 0x02;

    /**
     * Not has booking registered.
     */
    public const NOT_HAS_BOOKING = 0x03;
}
