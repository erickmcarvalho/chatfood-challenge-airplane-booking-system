<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Kernel\Database\Models\Model;

class Airplane extends Model
{
    use HasFactory;

    /**
     * Mapping of table columns to standardized queries.
     */
    public const COLUMN_MAPPING = [
        'id',
        'name',
        'company',
        'sits_number',
        'seat_columns'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'sits_number' => 'integer',
        'seat_columns' => 'integer'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'company',
        'sits_number',
        'seat_columns'
    ];

    /**
     * Returns the number of sits by airplane side.
     *
     * @return float|int
     */
    public function getSeatRowsAttribute()
    {
        return intval($this->sits_number / $this->seat_columns);
    }
}
