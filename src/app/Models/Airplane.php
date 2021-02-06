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
        'seat_sides'
    ];

    /**
     * Returns the number of sits by airplane side.
     *
     * @return float|int
     */
    public function getSeatRowsAttribute()
    {
        return $this->sits_number / $this->seat_columns;
    }
}
