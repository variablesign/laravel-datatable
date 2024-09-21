<?php

use Illuminate\Support\Facades\Route;
use VariableSign\DataTable\Http\Controllers\TableController;

Route::prefix(config('datatable.route.prefix'))
    ->middleware(config('datatable.route.middleware'))
    ->group(function () {
        Route::get(config('datatable.route.table.uri'), TableController::class)->name(config('datatable.route.table.name'));
    });