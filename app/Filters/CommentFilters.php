<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class CommentFilters extends QueryFilters
{
    public function search(string $value): Builder
    {
        return $this->builder->where(function (Builder $q) use ($value) {
            $q->where('body', 'like', "%{$value}%")
              ->orWhere('author_name', 'like', "%{$value}%");
        });
    }
}
