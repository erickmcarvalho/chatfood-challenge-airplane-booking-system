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
        'flight_id' => 'string',
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

    /**
     * Relationship with the flight.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function flight()
    {
        return $this->belongsTo(Flight::class);
    }

    /**
     * Relationship with the passenger.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function passenger()
    {
        return $this->belongsTo(Passenger::class);
    }

    /**
     * Relationship with the flight booking seats.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function seats()
    {
        return $this->hasMany(FlightBookingSeat::class);
    }
}
