<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Table Template
    |--------------------------------------------------------------------------
    |
    */

    'template' => 'default',

    /*
    |--------------------------------------------------------------------------
    | Save State
    |--------------------------------------------------------------------------
    |
    | Saves the state of table by storing query parameters of each request.
    |
    */

    'save_state' => false,

    /*
    |--------------------------------------------------------------------------
    | Save State Filter
    |--------------------------------------------------------------------------
    |
    | Set the names of the request parameters you want to exclude from being save.
    | Example: You can add "page" to the array if don't want to save the last viewed page.
    | You can use any of the keys from the "request_map" option below.
    |
    */

    'save_state_filter' => [
        'search'
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage
    |--------------------------------------------------------------------------
    |
    | Table states are stored in the browser's "local" storage by default.
    | You can set the value to "session" if you just want the stored 
    | data to be available per session.
    |
    */

    'storage' => 'local',

    /*
    |--------------------------------------------------------------------------
    | Deep Search
    |--------------------------------------------------------------------------
    |
    | Deep search breaks down search keywords and searches for each one separately.
    | Note: This affects the performance of search queries when your table
    | contains a huge amount of data.
    |
    */

    'deep_search' => false,

    /*
    |--------------------------------------------------------------------------
    | Per Page
    |--------------------------------------------------------------------------
    |
    | The default number of records to display per page in a table.
    |
    */

    'per_page' => 10,

    /*
    |--------------------------------------------------------------------------
    | Pagination Links
    |--------------------------------------------------------------------------
    |
    */

    'on_each_side' => 3,

    /*
    |--------------------------------------------------------------------------
    | Per Page Options
    |--------------------------------------------------------------------------
    |
    | Default for number of records to display per page options.
    | The maximum value in the array should have the same value as 'max_per_page'.
    |
    */

    'per_page_options' => [
        10 => 'Show 10 Entries', 
        25 => 'Show 25 Entries', 
        50 => 'Show 50 Entries'
    ],

    /*
    |--------------------------------------------------------------------------
    | Column Alignment
    |--------------------------------------------------------------------------
    |
    | Set the center and right alignment classes for aligning columns columns.
    | The "left" alignment is set as the default.
    |
    */

    'alignment' => [
        'left' => 'text-left',
        'center' => 'text-center',
        'right' => 'text-right'
    ],

    /*
    |--------------------------------------------------------------------------
    | Responsive Breakpoints
    |--------------------------------------------------------------------------
    |
    | Define responsive breakpoints used for showing/hiding table columns.
    | You can use the !important CSS flag when creating your CSS classes
    | to override similar classes applied to your table column.
    |
    */

    'breakpoints' => [
        'sm' => 'hidden sm:table-cell',
        'md' => 'hidden md:table-cell',
        'lg' => 'hidden lg:table-cell',
        'xl' => 'hidden xl:table-cell'
    ],

    /*
    |--------------------------------------------------------------------------
    | Fetch Request Route
    |--------------------------------------------------------------------------
    |
    */

    'route' => [
        'prefix' => 'datatable',
        'uri' => '/fetch/table/{table}',
        'name' => 'datatable.fetch.table',
        'middleware' => ['web']
    ],

    /*
    |--------------------------------------------------------------------------
    | Directory
    |--------------------------------------------------------------------------
    |
    */

    'directory' => 'DataTables',

    /*
    |--------------------------------------------------------------------------
    | Auto Update
    |--------------------------------------------------------------------------
    |
    | Auto refresh table after a set interval in seconds.
    |
    */

    'auto_update' => false,

    'auto_update_interval' => 60,

    /*
    |--------------------------------------------------------------------------
    | Request Mapping
    |--------------------------------------------------------------------------
    |
    | You can customize the request names to your preferred names.
    | Example: 'search' => 'q', 'order_column' => 'sort', 'per_page' => 'limit'
    | https://example.com?search=jane&order_column=name&per_page=25
    | ==>
    | https://example.com?q=jane&order=name&limit=25
    | 
    | NOTE: Remember to update the attributes section bellow if changes are made here
    |
    */

    'request_map' => [
        'page' => 'page',
        'search' => 'q',
        'order_column' => 'order_column',
        'order_direction' => 'order_direction',
        'per_page' => 'per_page',
        'filters' => 'filters'
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto Update On Filter
    |--------------------------------------------------------------------------
    |
    | Automatically send a request when a filter input value is changed.
    | Set to false if you want to manually add a button to trigger filter requests. 
    |
    */

    'auto_update_on_filter' => true,

    /*
    |--------------------------------------------------------------------------
    | Reference Data Attributes
    |--------------------------------------------------------------------------
    |
    | Data attributes for identifying various elements
    |
    */

    'references' => [
        'checkbox' => 'data-datatable-checkbox',
        'row' => 'data-datatable-row',
        'filters' => 'data-datatable-filter',
        'length' => 'data-datatable-per-page',
        'orderDirection' => 'data-datatable-order-direction',
        'orderColumn' => 'data-datatable-order-column',
        'pagination' => 'data-datatable-page-index',
        'search' => 'data-datatable-search-input',
        'hidden' => 'data-datatable-hidden'
    ],

    /*
    |--------------------------------------------------------------------------
    | Config Data Attributes
    |--------------------------------------------------------------------------
    |
    | Data attributes for setting config for the JavaScript component
    |
    */

    'config' => [
        'id' => ':id',
        'data-ui-datatable' => 'true',
        'data-datatable-url' => ':url',
        'data-datatable-save-state' => ':save_state',
        'data-datatable-save-state-filter' => ':save_state_filter',
        'data-datatable-auto-filter' => ':auto_filter',
        'data-datatable-request-map' => ':request_map',
        'data-datatable-references' => ':references'
    ],

];