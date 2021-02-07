<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Kernel\Database\Models\Model;

class FlightBookingSeat extends Model
{
    use HasFactory;

    /**
     * Mapping of table columns to standardized queries.
     */
    public const COLUMN_MAPPING = [
        'id',
        'flight_booking_id',
        'airplane_sit_id'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'airplane_sit_id' => 'integer'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'flight_booking_id',
        'airplane_sit_id'
    ];
}
