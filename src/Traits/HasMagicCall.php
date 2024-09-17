<?php

namespace VariableSign\DataTable\Traits;

trait HasMagicCall
{
    public function __call($name, $arguments)
    {
        return call_user_func([$this, $name], ...$arguments);
    }
}