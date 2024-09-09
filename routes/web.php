<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use VariableSign\DataTable\Helper;

Route::prefix(config('datatable.route.prefix'))
    ->middleware(config('datatable.route.middleware'))
    ->group(function () {
        Route::get(config('datatable.route.uri'), function (Request $request, string $table) {
            $class = Helper::getTableClass($table);
            $dd = new $class($request->get('data', []));
            dd($dd->render());
        })
        ->name(config('datatable.route.name'));
    });