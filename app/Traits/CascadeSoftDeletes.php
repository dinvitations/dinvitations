<?php

namespace App\Traits;

trait CascadeSoftDeletes
{
    protected static function bootCascadeSoftDeletes()
    {
        static::deleting(function ($model) {
            if (! $model->isForceDeleting()) {
                foreach ($model->getCascadeDeletes() as $relationName) {
                    $relation = $model->$relationName();

                    if (method_exists($relation, 'get')) {
                        $relation->get()->each->delete();
                    } else {
                        $related = $relation->first();
                        if ($related) {
                            $related->delete();
                        }
                    }
                }
            }
        });

        static::restoring(function ($model) {
            foreach ($model->getCascadeDeletes() as $relationName) {
                $relation = $model->$relationName();

                if (method_exists($relation, 'withTrashed')) {
                    $relation->withTrashed()->get()->each->restore();
                } else {
                    $related = $relation->withTrashed()->first();
                    if ($related) {
                        $related->restore();
                    }
                }
            }
        });
    }

    protected function getCascadeDeletes(): array
    {
        return [];
    }
}
