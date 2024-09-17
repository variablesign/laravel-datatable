<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use VariableSign\DataTable\Helper;

Route::prefix(config('datatable.route.prefix'))
    ->middleware(config('datatable.route.middleware'))
    ->group(function () {
        Route::get(config('datatable.route.uri'), function (Request $request, string $table) {
            $class = Helper::getTableData($table, 'table');

            if (!$class) {
                return response()->json([
                    'error'=> 'Session data has expired or not found.'
                ], 422);
            }

            $datatable = new $class();
            $datatable = new $class(Helper::getTableData($table, 'data', []));

            return $datatable->api();
        })
        ->name(config('datatable.route.name'));
    });