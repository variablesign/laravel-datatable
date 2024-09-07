<?php

namespace VariableSign\DataTable;

use Illuminate\Support\Facades\Facade;

/**
 * @see \VariableSign\DataTable\Skeleton\SkeletonClass
 */
class DataTableFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-datatable';
    }
}
