<?php

namespace VariableSign\DataTable\Filters;

use Illuminate\Support\Collection;
use VariableSign\DataTable\Traits\HasMagicGet;
use VariableSign\DataTable\Traits\HasMagicCall;
use Illuminate\Database\Eloquent\Builder as Eloquent;
use Illuminate\Database\Query\Builder as QueryBuilder;

class NumberFilter
{
    use HasMagicCall, HasMagicGet;

    private string $min = 'Min';

    private string $max = 'Max';

    private array $options = [];

    public function withOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

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
        if (array_key_exists('min', $value) && array_key_exists('max', $value)) {
            return $query->where($column, '>=', $value['min'])
                ->where($column, '<=', $value['max']);
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