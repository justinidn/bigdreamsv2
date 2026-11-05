<?php

namespace App\Traits;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

trait HasMasterActivityLog
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName(strtolower(class_basename($this)))
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
