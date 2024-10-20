<?php

namespace VariableSign\DataTable\Filters;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder as Eloquent;
use Illuminate\Database\Query\Builder as QueryBuilder;
use VariableSign\DataTable\Traits\InteractsWithFilter;

class BooleanFilter
{
    use InteractsWithFilter;
    
    private string $true = 'True';

    private string $false = 'False';

    private string $default = 'All';

    private function dataSource(): array
    {
        return [
            '' => $this->default,
            'true' => $this->true,
            'false' => $this->false
        ];
    }

    public function default(string $label): self
    {
        $this->default = $label;
        
        return $this;
    }

    public function true(?string $label = null): self
    {
        $this->true = $label;
        
        return $this;
    }

    public function false(?string $label = null): self
    {
        $this->false = $label;
        
        return $this;
    }

    private function getFilter(string $column, mixed $value, Eloquent|QueryBuilder|Collection $query): Eloquent|QueryBuilder|Collection
    {
        if (is_callable($this->builder)) {
            return call_user_func($this->builder, $query, $column, $value);
        }

        return match ($value) {
            'true' => $query->where($column, 1),
            'false' => $query->where($column, 0),
            default => $query
        };
    }

    private function getDataSource(): ?array
    {
        return $this->dataSource();
    }

    private function getElement(): array
    {
        return [
            'type' => 'select',
            'multiple' => false
        ];
    }
}