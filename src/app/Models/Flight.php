<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Kernel\Database\Models\Model;
use Kernel\Database\Models\Traits\UseUuidPrimaryKey;

class Flight extends Model
{
    use HasFactory;
    use UseUuidPrimaryKey;

    /**
     * Mapping of table columns to standardized queries.
     */
    public const COLUMN_MAPPING = [
        'id',
        'name',
        'source',
        'destination',
        'flight_date',
        'airplane_id'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'flight_date' => 'datetime',
        'airplane_id' => 'integer'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'id',
        'name',
        'source',
        'destination',
        'flight_date',
        'airplane_id'
    ];

    /**
     * Relationship with the airplanes.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function airplane()
    {
        return $this->belongsTo(Airplane::class);
    }
}
