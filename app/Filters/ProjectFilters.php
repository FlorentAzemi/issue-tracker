<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class ProjectFilters extends QueryFilters
{
    public function search(string $value): Builder
    {
        return $this->builder->where(function (Builder $q) use ($value) {
            $q->where('name', 'like', "%{$value}%")
              ->orWhere('description', 'like', "%{$value}%");
        });
    }

    public function trashed(string $value): Builder
    {
        if (filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
            return $this->builder->onlyTrashed();
        }

        return $this->builder;
    }
}
