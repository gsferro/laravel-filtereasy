<?php

namespace Gsferro\FilterEasy\Traits;

/**
 * @param $boolFilterFields
 * @param $likeFilterFields
 *
 * @package Gsferro\FilterEasy
 */
trait FilterEasy
{
    /**
     * Default attributes
     *
     * @return array
     */
    private function getBoolFilterFields(): array
    {
        return $this->boolFilterFields ?? [];
    }

    private function getLikeFilterFields(): array
    {
        return $this->likeFilterFields ?? [];
    }

    /**
     * Add filtering generic.
     *
     * @param  $builder : query builder.
     * @param  $filters : array of filters.
     * @return query builder.
     */
    public function scopeFilterEasy($builder, $filters = [])
    {
        if (!$filters) {
            return $builder;
        }
        $tableName             = $this->getTable();
        $defaultFillableFields = $this->fillable;
        foreach ($filters as $field => $value) {

            // validation
            if (empty($value)) {
                continue;
            }

            if (in_array($field, $this->getBoolFilterFields()) && $value != null) {
                $builder->where($field, (bool)$value);
                continue;
            }

            /*
            |---------------------------------------------------
            | Filter of date
            |---------------------------------------------------
            | using prefix :start and :end
            | exemple:
            |   created_at:start ->where(created_at, '>=', $value)
            |   updated_at:end ->where(updated_at, '<=', $value)
            */
            if (strpos($field, ":start")) {
                $field = str_replace(':start', '', $field);
                $builder->whereDate($tableName . '.' . $field, '>=', "$value");
                continue;
            }
            if (strpos($field, ":end")) {
                $field = str_replace(':end', '', $field);
                $builder->whereDate($tableName . '.' . $field, '<=', "$value");
                continue;
            }

            /*
            |---------------------------------------------------
            |  Has Relationship
            |---------------------------------------------------
            | exemple:
            |   posts:post_id
            |   $relName = posts
            |   $fk = post_id
            */
            if (strpos($field, ":")) {
                $relation = explode(":", $field);
                $relName  = $relation[ 0 ];
                $fk       = $relation[ 1 ];

                $builder = $builder->with($relName)
                    ->whereHas($relName, function ($query) use ($field, $fk, $value) {
                        if (in_array($field, $this->getLikeFilterFields())) {
                            $query->where($fk, 'LIKE', "%$value%");
                        } else {
                            $query->where($fk, $value);
                        }
                    });

                continue;
            }

            if (!in_array($field, $defaultFillableFields) || !$value) {
                continue;
            }

            if (in_array($field, $this->getLikeFilterFields())) {
                $builder->where($tableName . '.' . $field, 'LIKE', "%$value%");
            } else {
                if (is_array($value)) {
                    $builder->whereIn($field, $value);
                } else {
                    $builder->where($field, $value);
                }
            }
        }
        return $builder;
    }
}