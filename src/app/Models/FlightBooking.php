<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Kernel\Database\Models\Model;
use Kernel\Database\Models\Traits\UseUuidPrimaryKey;

class FlightBooking extends Model
{
    use HasFactory;
    use UseUuidPrimaryKey;

    /**
     * Mapping of table columns to standardized queries.
     */
    public const COLUMN_MAPPING = [
        'id',
        'flight_id',
        'passenger_id',
        'booking'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'flight_id' => 'integer',
        'passenger_id' => 'integer',
        'booking' => 'integer'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'id',
        'flight_id',
        'passenger_id',
        'booking'
    ];
}
