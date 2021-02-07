<?php

namespace Kernel\Database\Models\Traits;

use Illuminate\Support\Str;

trait UseUuidPrimaryKey
{
    /**
     * Trait Bootstrapping
     */
    protected static function bootUseUuidPrimaryKey()
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = Str::uuid();
            }
        });
    }

    /**
     * Trait initialization
     *
     * @returns void
     */
    protected function initializeUseUuidPrimaryKey()
    {
        $this->keyType = "string";
        $this->incrementing = false;
    }
}
