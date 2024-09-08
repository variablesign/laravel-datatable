<?php

namespace VariableSign\DataTable\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \VariableSign\DataTable\DataTable
 * @method static \VariableSign\DataTable\DataTable columns()
 * @see \VariableSign\DataTable\DataTable
 */
class DataTable extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \VariableSign\DataTable\DataTable::class;
    }
}
