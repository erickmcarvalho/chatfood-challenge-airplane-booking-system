<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Kernel\Database\Models\Model;

class AirplaneSit extends Model
{
    use HasFactory;

    /**
     * Mapping of table columns to standardized queries.
     */
    public const COLUMN_MAPPING = [
        'id',
        'airplane_id',
        'name',
        'seat_side',
        'row',
        'column'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'airplane_id' => 'integer',
        'seat_side' => 'integer',
        'row' => 'integer',
        'column' => 'integer'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'airplane_id',
        'name',
        'seat_side',
        'row',
        'column'
    ];

    /**
     * Relationship with the airplane.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function airplane()
    {
        return $this->belongsTo(Airplane::class);
    }
}
