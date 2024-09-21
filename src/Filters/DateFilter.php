<?php

namespace VariableSign\DataTable\Filters;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder as Eloquent;
use Illuminate\Database\Query\Builder as QueryBuilder;

class DateFilter
{
    private bool $range = false;

    private string $start= 'Start date';

    private string $end = 'End date';

    private string $default = 'Select date';

    private ?string $format = null;

    private array $options = [];

    public function withOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function start(string $placeholder): self
    {
        $this->start = $placeholder;
        
        return $this;
    }

    public function end(string $placeholder): self
    {
        $this->end = $placeholder;
        
        return $this;
    }

    public function default(string $placeholder): self
    {
        $this->default = $placeholder;
        
        return $this;
    }

    public function format(string $format): self
    {
        $this->format = $format;
        
        return $this;
    }

    public function range(): self
    {
        $this->range = true;
        
        return $this;
    }

    private function getFilter(string $column, mixed $value, Eloquent|QueryBuilder|Collection $builder): Eloquent|QueryBuilder|Collection
    {
        $value = $this->formatDate($value);

        if (is_array($value) && array_key_exists('start', $value) && array_key_exists('end', $value)) {
            return $builder->whereBetween($column, [$value['start'], $value['end']]);
        }
        
        if (is_array($value) && array_key_exists('start', $value)) {
            return $builder->where($column, '>=', $value['start']);
        }

        if (is_array($value) && array_key_exists('end', $value)) {
            return $builder->where($column, '<=', $value['end']);
        }

        if (!is_array($value)) {
            return $builder->where($column, $value);
        }

        return $builder;
    }

    private function getDataSource(): ?array
    {
        return [
            'default' => $this->default,
            'start' => $this->start,
            'end' => $this->end
        ];
    }

    private function getElement(): array
    {
        return [
            'type' => 'date',
            'range' => $this->range
        ];
    }

    private function formatDate(string|array $value): string|array
    {
        if (is_null($this->format)) {
            return $value;
        }

        if (is_array($value)) {
            return array_map(function ($date) {
                return now()->createFromFormat($this->format, $date)->format('Y-m-d');
            }, $value);
        }

        return now()->createFromFormat($this->format, $value)->format('Y-m-d');
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