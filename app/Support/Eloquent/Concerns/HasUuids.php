<?php

namespace App\Support\Eloquent\Concerns;

use Illuminate\Database\Eloquent\Concerns\HasUuids as LaravelHasUuids;

trait HasUuids
{
    use LaravelHasUuids;

    /**
     * Override the uniqueIds to generate ids only for
     * `uuid` column which will be a similar case
     * for all uuid supported columns on this project.
     */
    public function uniqueIds()
    {
        return ['uuid'];
    }
}
