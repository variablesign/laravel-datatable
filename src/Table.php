<?php

namespace VariableSign\DataTable;

class Table
{
    protected null|array $attributes = null;
    
    public function attributes(null|array $attributes = null): self
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function __get($name)
	{
		return $this->{$name};
	}
}