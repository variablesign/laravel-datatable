<?php

namespace VariableSign\DataTable\Filters;

use Illuminate\Support\Collection;
use VariableSign\DataTable\Traits\HasMagicGet;
use VariableSign\DataTable\Traits\HasMagicCall;
use Illuminate\Database\Eloquent\Builder as Eloquent;
use Illuminate\Database\Query\Builder as QueryBuilder;

class TextFilter
{
    use HasMagicCall, HasMagicGet;
    
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

    private function getFilter(string $column, mixed $value, Eloquent|QueryBuilder|Collection $query): Eloquent|QueryBuilder|Collection
    {
        $operator = '';

        if (is_array($value)) {
            $operator = data_get($value, 'operator', $operator);
            $value = data_get($value, 'value');
        }

        return match ($operator) {
            '' => $query->where($column, $value),
            'contains' => $query->where($column, 'like', '%' . $value . '%'),
            'starts_with' => $query->where($column, 'like', $value . '%'),
            'ends_with' => $query->where($column, 'like', '%' . $value),
            'not_equal_to' => $query->where($column, '<>', $value),
            'empty' => $query->where(function ($query) use ($column) {
                $query->orWhere($column, null)->orWhere($column, '');
            }),
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