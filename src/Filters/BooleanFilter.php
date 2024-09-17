<?php

namespace VariableSign\DataTable\Filters;

use Closure;
use Illuminate\Support\Collection;
use VariableSign\DataTable\Traits\HasMagicGet;
use VariableSign\DataTable\Traits\HasMagicCall;
use Illuminate\Database\Eloquent\Builder as Eloquent;
use Illuminate\Database\Query\Builder as QueryBuilder;

class BooleanFilter
{
    use HasMagicCall, HasMagicGet;

    private object $true;

    private object $false;

    private string $trueLabel = 'True';

    private string $falseLabel = 'False';

    private string $defaultLabel = 'All';

    private array $options = [];

    private function dataSource(): array
    {
        return [
            '' => $this->defaultLabel,
            'true' => $this->trueLabel,
            'false' => $this->falseLabel
        ];
    }

    public function withOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function default(string $label): self
    {
        $this->defaultLabel = $label;
        
        return $this;
    }

    public function true(?string $label = null, ?Closure $query = null): self
    {
        $this->trueLabel = $label;
        $this->true = $query;
        
        return $this;
    }

    public function false(?string $label = null, ?Closure $query = null): self
    {
        $this->falseLabel = $label;
        $this->false = $query;
        
        return $this;
    }

    private function getFilter(string $column, mixed $value, Eloquent|QueryBuilder|Collection $query): Eloquent|QueryBuilder|Collection
    {
        return match ($value) {
            'true' => $this->true ? call_user_func($this->true, $query) : $query->where($column, 1),
            'false' => $this->false ? call_user_func($this->false, $query) : $query->where($column, 0),
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