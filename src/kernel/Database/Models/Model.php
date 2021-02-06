<?php

namespace Kernel\Database\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

abstract class Model extends Eloquent
{
    /**
     * Mapping of table columns to standardized queries.
     */
    public const COLUMN_MAPPING = ['*'];
}
