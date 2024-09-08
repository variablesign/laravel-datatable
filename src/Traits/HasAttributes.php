<?php

namespace VariableSign\DataTable\Traits;
use Closure;

trait HasAttributes
{
    protected null|array|Closure $attributes = null;
    
    public function attributes(null|array|Closure $attributes = null): self
    {
        $this->attributes = $attributes;

        return $this;
    }
}