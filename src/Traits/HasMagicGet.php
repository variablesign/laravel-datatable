<?php

namespace VariableSign\DataTable\Traits;

trait HasMagicGet
{
    public function __get($name)
	{
		return $this->{$name};
	}
}