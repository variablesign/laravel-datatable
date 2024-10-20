<?php

namespace VariableSign\DataTable\Filters;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder as Eloquent;
use Illuminate\Database\Query\Builder as QueryBuilder;
use VariableSign\DataTable\Traits\InteractsWithFilter;

class TextFilter
{
    use InteractsWithFilter;

    private bool $operators = false;

    public function operators(): self
    {
        $this->operators = true;
        
        return $this;
    }

    private function getFilter(string $column, mixed $value, Eloquent|QueryBuilder|Collection $query): Eloquent|QueryBuilder|Collection
    {
        if (is_callable($this->builder)) {
            return call_user_func($this->builder, $query, $column, $value);
        }
        
        $operator = '';
        $isCollection = $query instanceof Collection;
        
        if (is_array($value)) {
            $operator = data_get($value, 'operator', $operator);
            $value = data_get($value, 'value');
        }

        return match ($operator) {
            '' => $query->where($column, $value),
            'contains' => $isCollection 
                ? $query->filter(function (array $item) use ($column, $value) {
                    return strpos($item[$column], $value) !== false;
                })
                : $query->where($column, 'like', '%' . $value . '%'),
            'starts_with' => $isCollection 
                ? $query->filter(function (array $item) use ($column, $value) {
                    return str_starts_with($item[$column], $value);
                })
                : $query->where($column, 'like', $value . '%'),
            'ends_with' => $isCollection 
                ? $query->filter(function (array $item) use ($column, $value) {
                    return str_ends_with($item[$column], $value);
                })
                : $query->where($column, 'like', '%' . $value),
            'not_equal_to' => $query->where($column, '<>', $value),
            'empty' => $query->whereIn($column, ['', null]),
            default => $query
        };
    }

    private function getDataSource(): ?array
    {
        return [
            '' => 'Equal to',
            'contains' => 'Contains',
            'starts_with' => 'Starts with',
            'ends_with' => 'Ends with',
            'not_equal_to' => 'Not equal to',
            'empty' => 'Empty'
        ];
    }

    private function getElement(): array
    {
        return [
            'type' => 'text',
            'operators' => $this->operators
        ];
    }
}