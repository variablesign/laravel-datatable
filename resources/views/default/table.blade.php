@if ($datatable->isNotEmpty($paginator) && !$datatable->isNotFound($paginator))
    <table {!! $datatable->formatAttributes($datatable->tableAttributes()) !!}>
        @if ($datatable->showTableHeader())
            <thead>
                <tr>
                    @foreach ($columns as $column)  
                        <th {!! $datatable->classAttributes([$column['alignment'], $column['responsive']]) !!}>
                            @if ($column['sortable'])
                                <a href="#" data-datatable-order-column="{{ $column['direction'] == 'asc' ? $column['alias'] : '' }}" data-datatable-order-direction="{{ $datatable->getNextSortDirection($column['direction'], $column['ordered']) }}">
                                    {{ $column['title'] }}

                                    @if ($column['ordered'] && $column['direction'] == 'desc')
                                        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25L12 21m0 0l-3.75-3.75M12 21V3" />
                                        </svg>                              
                                    @endif
                                        
                                    @if ($column['ordered'] && $column['direction'] == 'asc')
                                        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75L12 3m0 0l3.75 3.75M12 3v18" />
                                        </svg>                                                         
                                    @endif
                                </a>
                            @else 
                                @if ($column['checkbox']['enabled'])
                                    <input type="checkbox" data-datatable-checkbox="parent">
                                @else
                                    {{ $column['title'] }}
                                @endif
                            @endif
                        </th>  
                    @endforeach
                </tr>
            </thead>
        @endif

        <tbody>
            @foreach ($data as $index => $items)
                <tr {!! $datatable->formatAttributes($datatable->rowAttributes($items['model'], $index)) !!}>
                    @foreach ($items['columns'] as $column => $values)
                        @if ($values['checkbox']['enabled'])   
                            <td {!! $datatable->formatAttributes($values['attributes']) !!}>
                                <input 
                                    type="checkbox" 
                                    name="{{ $column }}[]" 
                                    value="{{ $values['value'] }}" 
                                    {!! $datatable->formatAttributes($values['checkbox']['attributes']) !!}
                                    data-datatable-checkbox="child">
                            </td>
                        @else
                            <td {!! $datatable->formatAttributes($values['attributes'], [$values['responsive'], $values['alignment']]) !!}>
                                {!! $values['value'] !!}
                            </td>
                        @endif
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

@if ($datatable->isNotFound($paginator))
    {!! $datatable->getNotFoundView() !!}
@endif

@if (!$datatable->isNotEmpty($paginator))
    {!! $datatable->getEmptyStateView() !!}
@endif