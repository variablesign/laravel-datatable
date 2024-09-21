<?php

namespace VariableSign\DataTable\Http\Controllers;


use Illuminate\Http\Request;
use VariableSign\DataTable\Helper;
use VariableSign\DataTable\Http\Controllers\Controller;

class TableController extends Controller
{
    public function __invoke(Request $request, string $table)
    {
        $class = Helper::getTableData($request, $table, 'table');

        if (!$class) {
            return response()->json([
                'error'=> 'Session data has expired or not found.'
            ], 422);
        }

        $datatable = new $class();
        $datatable = new $class(Helper::getTableData($request, $table, 'data', []));

        return response()->json($datatable->api());
    }
}
