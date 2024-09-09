<?php

namespace VariableSign\DataTable;

class Helper
{
    public static function getTableClass(string $table): string
	{
		$path = str($table)
            ->explode('.')
            ->transform(function (string $value, int $key) {
                return str($value)->studly()->toString();
            })
            ->join('\\');

        return '\\App\\' . config('datatable.directory') . '\\' . $path;
	}
}