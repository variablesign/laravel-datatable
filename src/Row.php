<?php

namespace VariableSign\DataTable;

use Closure;

class Row
{
    protected null|array|Closure $attributes = null;
    
    public function attributes(null|array|Closure $attributes = null): self
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function __get($name)
	{
		return $this->{$name};
	}
}