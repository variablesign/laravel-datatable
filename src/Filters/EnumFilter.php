<?php

namespace VariableSign\DataTable\Filters;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder as Eloquent;
use Illuminate\Database\Query\Builder as QueryBuilder;
use VariableSign\DataTable\Traits\InteractsWithFilter;

class EnumFilter
{
    use InteractsWithFilter;
    
    private array $dataSource = [];

    private string $defaultLabel = 'All';

    public function dataSource(array|Collection $dataSource): self
    {
        $this->dataSource = $dataSource instanceof Collection ? $dataSource->toArray() : $dataSource;
        
        return $this;
    }

    public function default(string $label): self
    {
        $this->defaultLabel = $label;
        
        return $this;
    }

    private function getFilter(string $column, mixed $value, Eloquent|QueryBuilder|Collection $query): Eloquent|QueryBuilder|Collection
    {
        if (is_callable($this->builder)) {
            return call_user_func($this->builder, $query, $column, $value);
        }
        
        return match ($value) {
            '' => $query,
            default => $query->where($column, $value)
        };
    }

    private function getDataSource(): ?array
    {
        $data = [
            '' => $this->defaultLabel
        ];

        foreach ($this->dataSource as $enum) {
            $data[$enum->value] = method_exists($enum, 'label') ? $enum->label() : $enum->value;
        }

        return $data;
    }

    private function getElement(): array
    {
        return [
            'type' => 'select',
            'multiple' => false
        ];
    }
}