<?php

namespace VariableSign\DataTable\Filters;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder as Eloquent;
use Illuminate\Database\Query\Builder as QueryBuilder;
use VariableSign\DataTable\Traits\InteractsWithFilter;

class NumberFilter
{
    use InteractsWithFilter;
    
    private string $min = 'Min';

    private string $max = 'Max';

    public function min(string $placeholder): self
    {
        $this->min = $placeholder;
        
        return $this;
    }

    public function max(string $placeholder): self
    {
        $this->max = $placeholder;
        
        return $this;
    }

    private function getFilter(string $column, mixed $value, Eloquent|QueryBuilder|Collection $query): Eloquent|QueryBuilder|Collection
    {
        if (is_callable($this->builder)) {
            return call_user_func($this->builder, $query, $column, $value);
        }
        
        if (array_key_exists('min', $value) && array_key_exists('max', $value)) {
            return $query->whereBetween($column, [$value['min'], $value['max']]);
        }
        
        if (array_key_exists('min', $value)) {
            return $query->where($column, '>=', $value['min']);
        }

        if (array_key_exists('max', $value)) {
            return $query->where($column, '<=', $value['max']);
        }

        return $query;
    }

    private function getDataSource(): ?array
    {
        return [
            'min' => $this->min,
            'max' => $this->max
        ];
    }

    private function getElement(): array
    {
        return [
            'type' => 'number',
            'range' => true
        ];
    }
}