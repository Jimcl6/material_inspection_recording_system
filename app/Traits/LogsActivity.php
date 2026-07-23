<?php

namespace App\Traits;

use App\Services\ActivityService;
use Illuminate\Database\Eloquent\Model;

trait LogsActivity
{
    /**
     * Boot the trait.
     */
    protected static function bootLogsActivity()
    {
        static::created(function (Model $model) {
            ActivityService::logCreate($model);
        });

        static::updated(function (Model $model) {
            if ($model->wasChanged()) {
                $changes = $model->getChanges();
                unset($changes['updated_at']);
                if (!empty($changes)) {
                    ActivityService::logUpdate($model, $changes);
                }
            }
        });

        static::deleted(function (Model $model) {
            ActivityService::logDelete($model);
        });
    }

    /**
     * Get the identifier attribute for activity logging.
     */
    public function getIdentifierAttribute(): string
    {
        if (isset($this->name)) {
            return $this->name;
        }
        
        if (isset($this->id)) {
            return '# ' . $this->id;
        }
        
        return 'Record';
    }
}
