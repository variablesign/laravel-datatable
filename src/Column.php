<?php

namespace VariableSign\DataTable;

use Closure;
use VariableSign\DataTable\Traits\HasAttributes;
use VariableSign\DataTable\Traits\HasMagicGet;

class Column
{
    use HasAttributes, HasMagicGet;
    
    protected ?string $name = null;

    protected ?string $alias = null;

    protected ?string $title = null;

    protected bool $index = false;

    protected bool $checkbox = false;

    protected ?Closure $edit = null;

    protected ?string $responsive = null;

    protected ?string $alignment = null;

    protected bool|array|Closure $searchable = false;

    protected bool|array|Closure $sortable = false;

    protected bool|Closure $filterable = false;

    // protected null|array|Closure $colgroup = null;

    protected null|array|Closure $checkboxAttributes = null;

    public function name(string $name): self
    {
        $this->name = $name;
        $this->alias = $name;

        return $this;
    }

    public function alias(string $alias): self
    {
        $this->alias = $alias;

        return $this;
    }

    public function title(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function index(): self
    {
        $this->index = true;

        return $this;
    }

    public function edit(Closure $edit): self
    {
        $this->edit = $edit;

        return $this;
    }

    public function searchable(bool|array|Closure $searchable = true): self
    {
        $searchable = $searchable === true ? [$this->name] : $searchable;
        $this->searchable = $searchable;

        return $this;
    }

    public function sortable(bool|array|Closure $sortable = true): self
    {
        $sortable = $sortable === true ? [$this->name] : $sortable;
        $this->sortable = $sortable;

        return $this;
    }

    public function filterable(Closure $filterable): self
    {
        $this->filterable = $filterable;

        return $this;
    }

    // public function colgroup(null|array|Closure $attributes = null): self
    // {
    //     $this->colgroup = $attributes;

    //     return $this;
    // }

    public function responsive(string $breakpoint): self
    {
        $this->responsive = $breakpoint;

        return $this;
    }

    public function align(string $alignment): self
    {
        $this->alignment = $alignment;

        return $this;
    }

    public function checkbox(null|array|Closure $attributes = null): self
    {
        $this->checkbox = true;
        $this->checkboxAttributes = $attributes;

        return $this;
    }
}