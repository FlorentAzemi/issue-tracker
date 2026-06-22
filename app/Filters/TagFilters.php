<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class TagFilters extends QueryFilters
{
    public function search(string $value): Builder
    {
        return $this->builder->where('name', 'like', "%{$value}%");
    }
}
