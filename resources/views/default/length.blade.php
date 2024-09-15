@if (!$datatable->isNotFound($paginator))
    @foreach ($datatable->getPerPageOptions() as $value => $text)
        <button type="button" data-datatable-per-page="{{ $value }}" @disabled($paginator->perPage() == $value)>
            {{ $text }}
        </button>  
    @endforeach
@endif