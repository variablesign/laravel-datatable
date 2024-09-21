<?php

namespace VariableSign\DataTable\Filters;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder as Eloquent;
use Illuminate\Database\Query\Builder as QueryBuilder;

class SelectFilter
{
    private array $dataSource = [];

    private string $value = 'id';

    private string $label = 'name';

    private string $defaultLabel = 'All';

    private array $options = [];

    public function dataSource(array|Collection $dataSource): self
    {
        $this->dataSource = $dataSource instanceof Collection ? $dataSource->toArray() : $dataSource;

        return $this;
    }

    public function withOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function value(string $value): self
    {
        $this->value = $value;
        
        return $this;
    }

    public function label(string $label): self
    {
        $this->label = $label;
        
        return $this;
    }

    public function default(string $label): self
    {
        $this->defaultLabel = $label;
        
        return $this;
    }

    private function getFilter(string $column, mixed $value, Eloquent|QueryBuilder|Collection $builder): Eloquent|QueryBuilder|Collection
    {
        return match ($value) {
            '' => $builder,
            default => $builder->where($column, $value)
        };
    }

    private function getDataSource(): ?array
    {
        $data = [
            '' => $this->defaultLabel
        ];

        foreach ($this->dataSource as $options) {
            $data[$options[$this->value]] = $options[$this->label];
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

    public function __call($name, $arguments)
    {
        return call_user_func([$this, $name], ...$arguments);
    }
	
    public function __get($name)
	{
		return $this->{$name};
	}
}