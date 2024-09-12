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

    public static function getFullTableName(object $class, string $separator = '.'): string
    {
        return str(get_class($class))
            ->replaceFirst('App\\' . config('datatable.directory') . '\\', '')
            ->explode('\\')
            ->transform(function (string $value, int $key) {
                return str($value)->kebab()->toString();
            })
            ->join($separator);
    }

    public static function getTableData(string $table, ?string $key = null, mixed $default = null): mixed
    {
        $table = str($table)->replace('.', '-')->toString();
        $sessionKey = 'datatable.' . $table;
        $key = $key ? '.' . $key : '';

        if (session()->exists($sessionKey)) {
            return session()->get($sessionKey . $key, $default);
        }

        return null;
    }
}