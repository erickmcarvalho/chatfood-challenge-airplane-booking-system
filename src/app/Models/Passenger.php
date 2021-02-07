<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Kernel\Database\Models\Model;

class Passenger extends Model
{
    use HasFactory;

    /**
     * Mapping of table columns to standardized queries.
     */
    public const COLUMN_MAPPING = [
        'id',
        'name',
        'email'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'id',
        'name',
        'email'
    ];
}
