<?php

namespace VariableSign\DataTable\Filters;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder as Eloquent;
use Illuminate\Database\Query\Builder as QueryBuilder;

class NumberFilter
{
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

    private function getFilter(string $column, mixed $value, Eloquent|QueryBuilder|Collection $builder): Eloquent|QueryBuilder|Collection
    {
        if (array_key_exists('min', $value) && array_key_exists('max', $value)) {
            return $builder->whereBetween($column, [$value['min'], $value['max']]);
        }
        
        if (array_key_exists('min', $value)) {
            return $builder->where($column, '>=', $value['min']);
        }

        if (array_key_exists('max', $value)) {
            return $builder->where($column, '<=', $value['max']);
        }

        return $builder;
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

    public function __call($name, $arguments)
    {
        return call_user_func([$this, $name], ...$arguments);
    }
	
    public function __get($name)
	{
		return $this->{$name};
	}
}