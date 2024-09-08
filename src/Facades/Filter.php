<?php

namespace VariableSign\DataTable\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \VariableSign\DataTable\Filter
 * @method static \VariableSign\DataTable\Filters\BooleanFilter boolean()
 * @method static \VariableSign\DataTable\Filters\DateFilter date()
 * @method static \VariableSign\DataTable\Filters\EnumFilter enum()
 * @method static \VariableSign\DataTable\Filters\NumberFilter number()
 * @method static \VariableSign\DataTable\Filters\SelectFilter select()
 * @method static \VariableSign\DataTable\Filters\TextFilter text()
 * @see \VariableSign\DataTable\Filter
 */
class Filter extends Facade
{
    /**
     * Indicates if the resolved instance should be cached.
     *
     * @var bool
     */
    protected static $cached = false;

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \VariableSign\DataTable\Filter::class;
    }
}