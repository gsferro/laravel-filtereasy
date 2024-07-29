<?php

namespace Gsferro\FilterEasy\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * @param  array<int, string>  $likeFilterFields
 * @param  array<int, string>  $boolFilterFields
 *
 * @author Gsferro
 * @package Gsferro\FilterEasy
 */
trait FilterEasy
{
    /**
     * Apply filters to the query builder.
     *
     * @param  Builder  $builder  The query builder instance.
     * @param  array  $filters  The array of filters.
     * @return Builder The modified query builder instance.
     */
    public function scopeFilterEasy(Builder $builder, array $filters = []): Builder
    {
        if (empty($filters)) {
            return $builder;
        }

        foreach ($filters as $field => $value) {
            /*
            |---------------------------------------------------
            |  Validation
            |---------------------------------------------------
            */
            // nullable value (first for performance)
            if (is_null($value)) {
                continue;
            }

            // default fields in fillable
            if (!$this->checkFieldInFillableOrRelation($field)) {
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
                $builder->where($field, '>=', $value);
                continue;
            }
            if (strpos($field, ":end")) {
                $field = str_replace(':end', '', $field);
                $builder->where($field, '<=', $value);
                continue;
            }

            /*
            |---------------------------------------------------
            |  Has Relationship
            |---------------------------------------------------
            | exemple:
            |   posts:title
            |   relationName = posts
            |   relationField = title
            */
            if (strpos($field, ":")) {
                $rel = explode(":", $field);
                $relationName = $rel[0];
                $relationField = $rel[1];

                $builder = $builder
                    ->with($relationName)
                    ->whereHas($relationName, function ($query)
                    use ($field, $relationField, $value) {
                        if ($this->checkFieldInLikeFilterFields($field)) {
                            $query->where($relationField, 'LIKE', "%$value%");
                        } else {
                            $query->where($relationField, $value);
                        }
                    });

                continue;
            }

            // bool
            if ($this->checkFieldInBoolFilterFields($field)) {
                $builder->where($field, (bool)$value);
                continue;
            }

            // Like
            if ($this->checkFieldInLikeFilterFields($field)) {
                $builder->where($field, 'LIKE', "%$value%");
                continue;
            }

            // in
            if (is_array($value)) {
                $builder->whereIn($field, $value);
                continue;
            }

            // exact
            $builder->where($field, $value);
        }

        // return query builder
        return $builder;
    }

    /**
     * Retrieves the array of fields that are used for boolean filtering.
     *
     * @return array The array of fields.
     */
    private function getBoolFilterFields(): array
    {
        return $this->boolFilterFields ?? [];
    }

    /**
     * Retrieves the array of fields that are used for LIKE filtering.
     *
     * @return array The array of fields.
     */
    private function getLikeFilterFields(): array
    {
        return $this->likeFilterFields ?? [];
    }

    /**
     * Checks if a given field is either a relation or in the fillable array.
     *
     * @param  string  $field  The field to check.
     * @return bool Returns true if the field is a relation or in the fillable array, false otherwise.
     */
    private function checkFieldInFillableOrRelation(string $field): bool
    {
        // remove a condição especial de :start | :end e relation
        if (strpos($field, ":")) {
            $field = explode(":", $field)[0];
        }

        // check if field is relation
        if (method_exists(__CLASS__, $field)) {
            return true;
        }

        // check if field is in fillable
        return in_array($field, $this->getFillable());
    }

    /**
     * Checks if a given field is in the like filter fields.
     *
     * @param  string  $field  The field to check.
     * @return bool Returns true if the field is in the like filter fields, false otherwise.
     */
    private function checkFieldInLikeFilterFields(string $field): bool
    {
        // check if field is in checkFieldInLikeFilterFields
        return in_array($field, $this->getLikeFilterFields());
    }

    /**
     * Checks if a given field is in the bool filter fields.
     *
     * @param  string  $field  The field to check.
     * @return bool Returns true if the field is in the bool filter fields, false otherwise.
     */
    private function checkFieldInBoolFilterFields(string $field): bool
    {
        // check if field is in boolFilterFields
        return in_array($field, $this->getBoolFilterFields());
    }
}
