<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class QueryFilters
{
    protected Request $request;
    protected Builder $builder;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function apply(Builder $builder): Builder
    {
        $this->builder = $builder;

        foreach ($this->filters() as $name => $value) {
            if (!method_exists($this, $name)) {
                continue;
            }
            if (is_array($value)) {
                $this->$name($value);
            } elseif (strlen((string) $value)) {
                $this->$name($value);
            }
        }

        return $this->builder;
    }

    public function filters(): array
    {
        if (is_array($this->request->filter)) {
            return array_filter($this->request->filter, fn($v) => $v !== null && $v !== '');
        }

        return [];
    }
}
