<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use VariableSign\DataTable\Helper;

Route::prefix(config('datatable.route.prefix'))
    ->middleware(config('datatable.route.middleware'))
    ->group(function () {
        Route::get(config('datatable.route.uri'), function (Request $request, string $table) {
            // dd($request->session()->all(), Helper::getTableData($table));
            $class = Helper::getTableData($table, 'table');
            $dd = new $class(Helper::getTableData($table, 'data', []));
            dd($dd);
        })
        ->name(config('datatable.route.name'));
    });