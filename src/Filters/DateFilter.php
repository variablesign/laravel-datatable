<?php

namespace VariableSign\DataTable\Filters;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder as Eloquent;
use Illuminate\Database\Query\Builder as QueryBuilder;
use VariableSign\DataTable\Traits\InteractsWithFilter;

class DateFilter
{
    use InteractsWithFilter;

    private bool $range = false;

    private string $start= 'Start date';

    private string $end = 'End date';

    private string $default = 'Select date';

    private ?string $format = null;

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

    private function getFilter(string $column, mixed $value, Eloquent|QueryBuilder|Collection $query): Eloquent|QueryBuilder|Collection
    {
        $value = $this->formatDate($value);

        if (is_callable($this->builder)) {
            return call_user_func($this->builder, $query, $column, $value);
        }

        if (is_array($value) && array_key_exists('start', $value) && array_key_exists('end', $value)) {
            return $query->whereBetween($column, [$value['start'], $value['end']]);
        }
        
        if (is_array($value) && array_key_exists('start', $value)) {
            return $query->where($column, '>=', $value['start']);
        }

        if (is_array($value) && array_key_exists('end', $value)) {
            return $query->where($column, '<=', $value['end']);
        }

        if (!is_array($value)) {
            return $query->where($column, $value);
        }

        return $query;
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
}