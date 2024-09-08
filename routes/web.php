<?php

use Illuminate\Support\Facades\Route;

Route::prefix(config('datatable.route.prefix'))
    ->middleware(config('datatable.route.middleware'))
    ->group(function () {
        Route::get(config('datatable.route.uri'), function (string $table) {
            $path = str($table)
                ->explode('.')
                ->transform(function (string $value, int $key) {
                    return str($value)->studly()->toString();
                })
                ->join('\\');

            $class = '\\App\\' . config('datatable.directory') . '\\' . $path;

            // $dd = new $class();
            // dd($dd);
        })
        ->name(config('datatable.route.name'));
    });