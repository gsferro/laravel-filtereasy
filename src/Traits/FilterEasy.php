<?php

namespace Gsferro\FilterEasy\Traits;

trait FilterEasy
{
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
            if (in_array($field, $this->boolFilterFields) && $value != null) {
                $builder->where($field, (bool)$value);
                continue;
            }
            if (!in_array($field, $defaultFillableFields) || !$value) {
                continue;
            }

            /*
            |---------------------------------------------------
            |  Has Relationship
            |---------------------------------------------------
            | exemple:
            |   posts.post_id
            |   $relName = posts
            |   $fk = post_id
            */
            if (strpos($field, ".")) {
                $relation = explode(".", $field);
                $relName  = $relation[ 0 ];
                $fk       = $relation[ 1 ];
                //insere o relacionamento na query
                $builder = $builder->with($relName)
                    ->whereHas($relName, function ($query) use ($fk, $value) {
                        $query->where($fk, $value);
                    });

                continue;
            }

            /*
            |---------------------------------------------------
            | Filter of date
            |---------------------------------------------------
            | using prefix _start and _end
            | exemple:
            |   created_at_start ->where(created_at, '>=', $value)
            |   updated_at_end ->where(updated_at, '<=', $value)
            */
            if (strpos($field, "_start")) {
                $field = str_replace('_start', '', $field);
                $builder->where($tableName . '.' . $field, '>=', "$value");
                continue;
            }
            if (strpos($field, "_end")) {
                $field = str_replace('_end', '', $field);
                $builder->where($tableName . '.' . $field, '<=', "$value");
                continue;
            }

            if (in_array($field, $this->likeFilterFields)) {
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