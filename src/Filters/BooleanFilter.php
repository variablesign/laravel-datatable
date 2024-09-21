<?php

namespace VariableSign\DataTable\Filters;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder as Eloquent;
use Illuminate\Database\Query\Builder as QueryBuilder;

class BooleanFilter
{
    private ?object $true = null;

    private ?object $false = null;

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

    public function true(?string $label = null, ?Closure $callback = null): self
    {
        $this->trueLabel = $label;
        $this->true = $callback;
        
        return $this;
    }

    public function false(?string $label = null, ?Closure $callback = null): self
    {
        $this->falseLabel = $label;
        $this->false = $callback;
        
        return $this;
    }

    private function getFilter(string $column, mixed $value, Eloquent|QueryBuilder|Collection $builder): Eloquent|QueryBuilder|Collection
    {
        return match ($value) {
            'true' => $this->true ? call_user_func($this->true, $builder) : $builder->where($column, 1),
            'false' => $this->false ? call_user_func($this->false, $builder) : $builder->where($column, 0),
            default => $builder
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

    public function __call($name, $arguments)
    {
        return call_user_func([$this, $name], ...$arguments);
    }
	
    public function __get($name)
	{
		return $this->{$name};
	}
}