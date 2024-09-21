<?php

namespace VariableSign\DataTable\Filters;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder as Eloquent;
use Illuminate\Database\Query\Builder as QueryBuilder;

class TextFilter
{
    private bool $operators = false;

    private array $options = [];

    public function withOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function operators(): self
    {
        $this->operators = true;
        
        return $this;
    }

    private function getFilter(string $column, mixed $value, Eloquent|QueryBuilder|Collection $builder): Eloquent|QueryBuilder|Collection
    {
        $operator = '';
        $isCollection = $builder instanceof Collection;
        
        if (is_array($value)) {
            $operator = data_get($value, 'operator', $operator);
            $value = data_get($value, 'value');
        }

        return match ($operator) {
            '' => $builder->where($column, $value),
            'contains' => $isCollection 
                ? $builder->filter(function (array $item) use ($column, $value) {
                    return strpos($item[$column], $value) !== false;
                })
                : $builder->where($column, 'like', '%' . $value . '%'),
            'starts_with' => $isCollection 
                ? $builder->filter(function (array $item) use ($column, $value) {
                    return str_starts_with($item[$column], $value);
                })
                : $builder->where($column, 'like', $value . '%'),
            'ends_with' => $isCollection 
                ? $builder->filter(function (array $item) use ($column, $value) {
                    return str_ends_with($item[$column], $value);
                })
                : $builder->where($column, 'like', '%' . $value),
            'not_equal_to' => $builder->where($column, '<>', $value),
            'empty' => $builder->whereIn($column, ['', null]),
            default => $builder
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

    public function __call($name, $arguments)
    {
        return call_user_func([$this, $name], ...$arguments);
    }
	
    public function __get($name)
	{
		return $this->{$name};
	}
}