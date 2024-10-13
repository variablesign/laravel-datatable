<?php

namespace VariableSign\DataTable;

use Illuminate\View\View;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder as Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;

abstract class DataTable
{
    protected string $tableName = 'datatable';

    protected ?string $tableId = null;

    protected ?string $queryStringPrefix = null;

    protected ?string $orderColumn = null;

    protected string $orderDirection = 'asc';

    protected ?int $perPage = null;

    protected ?array $perPageOptions = null;

    protected ?int $onEachSide = null;

    protected ?bool $deepSearch = null;

    protected ?bool $saveState = null;

    protected ?array $saveStateFilter = null;

    protected ?bool $autoUpdateOnFilter = null;

    protected ?string $template = null;

    protected bool $showTotal = true;

    protected bool $showHeader = true;

    protected bool $showPageOptions = true;

    protected bool $showInfo = true;

    protected bool $showPagination = true;

    protected ?string $searchPlaceholder = null;

    private Eloquent|QueryBuilder|Collection $dataSource;

    private Collection $columns;

    private Collection $setups;

    private ?string $defaultOrderColumn = null;

    private array $data;

    public function __construct(array $data = [])
    {
        $this->data = $this->setData($data);
        $this->perPageOptions ??= $this->config('per_page_options');
        $this->perPage = $this->setPerPage();
        $this->defaultOrderColumn = $this->orderColumn;
        $this->columns = $this->setColumns();
        $this->setups = $this->setSetups();
        $this->orderColumn = $this->getColumn($this->setOrderColumn(), 'name');
        $this->orderDirection = $this->setOrderDirection();
        $this->deepSearch ??= $this->config('deep_search');
        $this->autoUpdateOnFilter ??= $this->config('auto_update_on_filter');
        $this->saveStateFilter ??= $this->config('save_state_filter');
    }

    public function getTableId(): string
    {
        return $this->tableId ?? Helper::getFullTableName($this, '-');
    }

    public function api(): array
    {
        $paginator = $this->paginator();
        $data = [
            'data' => $this->transformer($paginator),
            'columns' => $this->columns->values()->all(),
            'filters' => $this->getFilter(),
            'datatable' => $this,
            'paginator' => $paginator
        ];

        return [
            'isNotEmpty' => $this->isNotEmpty($data['paginator']),
            'isNotFound' => $this->isNotFound($data['paginator']),
            'filtered' => $this->getActiveFilterCount(),
            'request' => request()->all(),
            'save' => $this->getSaveableRequest(),
            'html' => [
                'table' =>  view($this->view('table'), $data)->render(),
                'info' => view($this->view('info'), $data)->render(),
                'pagination' => view($this->view('pagination'), $data)->render(),
                'length' => view($this->view('length'), $data)->render(),
                'search' => view($this->view('search'), $data)->render(),
                'filters' => view($this->view('filters'), $data)->render()
            ]
        ];
    }

    public function render(): ?string
    {
        $build = '';
        $attributes = collect($this->config('config'));
        $attributes = $attributes->map(function (string $item, string $key) {
            return __($item, [
                'id' => $this->getTableId(),
                'url' => route($this->config('route.table.name'), [
                    Helper::getFullTableName($this)
                ]),
                'auto_filter' => $this->autoUpdateOnFilter,
                'auto_filter_delay' => $this->config('auto_filter_delay'),
                'save_state' => $this->saveState ?? $this->config('save_state') ? 'true' : 'false',
                'save_state_filter' => json_encode($this->saveStateFilter ?? $this->config('save_state_filter')),
                'min_search_length' => $this->config('min_search_length'),
                'search_delay' => $this->config('search_delay'),
                'request_map' =>  json_encode($this->getRequestMap()),
                'references' => json_encode($this->config('references'))
            ]);
        });

        foreach ($attributes as $key => $value) {
            $build .= is_array(json_decode($value, true))
                ? $key . "='" . $value . "' " 
                : $key . '="' . $value . '" ';
        }

        return trim($build);
    }

    public function isNotEmpty(Paginator $paginator): bool
    {
        return $paginator->isNotEmpty() 
            || ($paginator->isEmpty() && $this->request('search'))
            || ($paginator->isEmpty() && $this->request('filters'));
    }

    public function isNotFound(Paginator $paginator): bool
    {
        return ($paginator->isEmpty() && $this->request('search')) 
            || ($paginator->isEmpty() && $this->request('filters'));
    }

    public function formatAttributes(?array $attributes = null, string|array $mergeClass = null): string 
    {
        $attributes = $attributes ?: [];
        $mergeClass = is_array($mergeClass) ? implode(' ', array_filter($mergeClass)) : $mergeClass;
        $build = '';

        if ($mergeClass) {
            if (array_key_exists('class', $attributes)) {
                $attributes = collect($attributes)
                    ->map(function (string $value, string $key) use ($mergeClass) {
                        return $key === 'class' ? $value . ' ' . $mergeClass : $value;
                    })
                    ->all();
            } else {
                $attributes['class'] = $mergeClass;
            }
        }

        foreach ($attributes as $key => $value) {
            if ($value !== null) {
                $build .= is_int($key) 
                    ? $value . '="" '
                    : $key . '="' . $value . '" ';
            }
        }

        return trim($build);
    }

    public function classAttributes(?array $classes = null): string 
    {
        $classes = $classes ?? [];
        $classes = array_filter($classes);
        $classes = count($classes) > 0 ? trim(implode(' ', $classes)) : null;

        return !is_null($classes) ? 'class="' . $classes . '"' : '';
    }

    public function tableAttributes(string $appendClasses = null): array
    {
        $tableSetup = data_get($this->setups, Table::class);

        if (is_array($tableSetup?->attributes)) {
            if (data_get($tableSetup->attributes, 'class') && $appendClasses) {
                return array_merge($tableSetup->attributes, [
                    'class' => $tableSetup->attributes['class'] . ' ' . $appendClasses
                ]);
            }

            return $tableSetup->attributes;
        }

        return $appendClasses ? ['class' => $appendClasses] : [];
    }

    public function rowAttributes(mixed $model, mixed $index): array
    {
        $rowSetup = data_get($this->setups, Row::class);

        if (is_callable($rowSetup?->attributes)) {
            return call_user_func($rowSetup->attributes, $model, $index);
        }

        return is_array($rowSetup?->attributes) ? $rowSetup->attributes : [];
    }

    public function getFilter(?string $column = null): array
    {
        $filterable = $this->getFilterableColumns();
        $filterable->transform(function (array $item, string $key) {
            $filter = $item['filterable'];
            
            return [
                'title' => $item['title'],
                'value' => data_get($this->request('filters'), $key, ''),
                'element' => is_object($filter) ? $filter->getElement() : null,
                'data' => is_object($filter) ? $filter->getDataSource() : null,
                'options' => is_object($filter) ? $filter->options : []
            ];
        });

        return $filterable->all();
    }

    public function getNextSortDirection(?string $direction, bool $ordered): string
    {
        if (!$ordered) {
            return 'asc';
        }

        return match ($direction) {
            '' => 'asc',
            'asc' => 'desc',
            'desc' => '',
            default => ''
        };
    }

    public function getData(?string $key = null, mixed $default = null): mixed
    {
        return data_get($this->data, $key, $default) ?? $this->data;
    }

    public function getRequestMap(?string $key = null): string|array|null
    {
        $map = $this->config('request_map');

        if ($this->queryStringPrefix) {
            $map = array_map(function ($item) {
                return $this->queryStringPrefix . '_' . $item;
            }, $map);
        }

        return $key ? data_get($map, $key) : $map;
    }

    public function request(string $key): null|string|array
    {
        $request = data_get($this->getRequestMap(), $key);

        return is_null($request) ? null : request()->get($request);
    }

    public function getNotFoundView()
    {
        $notFound = $this->notFound($this->request('search'));

        return $notFound instanceof View ? $notFound->render() : $notFound;
    }

    public function getEmptyStateView()
    {
        $emptyState = $this->emptyState();

        return $emptyState instanceof View ? $emptyState->render() : $emptyState;
    }

    public function getPerPageOptions(): array
    {
        return $this->perPageOptions;
    }

    public function getTotalFilters(): int
    {
        return $this->getFilterableColumns()->count();
    }

    public function getActiveFilterCount(): int
    {
        return collect($this->request('filters') ?? [])->count();
    }

    public function getSearchPlaceholder(): string
    {
        $searchable = $this->getSearchableColumns();
        $by = $searchable->isEmpty() ? '' : ' by ';
        $search = $searchable->pluck('alias')
            ->map(fn (string $item) => str_replace('_', ' ', $item))
            ->join(', ', ' or ');

        return $this->searchPlaceholder ?: 'Search ' . $this->tableName . $by . $search . '...';
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function getTemplate(): string
    {
        return $this->template ?? $this->config('template');
    }

    public function showTableHeader(): bool
    {
        return $this->showHeader;
    }

    public function showTotalItems(): bool
    {
        return $this->showTotal;
    }

    public function linksOnEachSide(): number
    {
        return $this->onEachSide ?? $this->config('on_each_side');
    }

    protected function setup(): array
    {
        return [];
    }

    protected function columns(): array
    {
        return [];
    }

    protected function dataSource(): Eloquent|QueryBuilder|Collection
    {
        return Model::query();
    }

    protected function emptyState(): string|View
    {
        return view($this->view('empty'));
    }

    protected function notFound(?string $keyword): string|View
    {
        return view($this->view('not-found'), [
            'keyword' => $keyword,
            'table' => $this->tableName
        ]);
    }

    private function config(?string $key = null, mixed $default = null): mixed
    {
        $key = $key ? 'datatable.' . $key : 'datatable';

        return config($key, $default);
    }

    private function setData(array $data): array
    {   
        $key = 'datatable.' . Helper::getFullTableName($this, '-');

        request()->session()->put($key, [
            'table' => get_class($this),
            'data'=> $data
        ]);

        return $data;
    }

    private function setColumns(): Collection
    {
        $columns = collect($this->columns());
        $columns = $columns->transform(function (object $item, int $key) {
            return [
                'name' => $item->name,
                'alias' => $item->alias,
                'title' => $item->title,
                'searchable' => $item->searchable,
                'sortable' => $item->sortable,
                'filterable' => $this->setFilterableColumn($item),
                'ordered' => $item->sortable && $this->setOrderColumn() === $item->alias ? true : false,
                'direction' => $this->setOrderColumn() === $item->alias ? $this->setOrderDirection() : 'asc',
                'edit' => $item->edit,
                'index' => $item->index,
                'checkbox' => [
                    'enabled' => $item->checkbox,
                    'attributes' => $item->checkboxAttributes
                ],
                'attributes' => $item->attributes,
                'responsive' => $this->config('breakpoints.' . $item->responsive),
                'alignment' => $this->config('alignment.' . $item->alignment) ?? $this->config('alignment.left')
            ];
        });

        return $columns->keyBy('alias');
    }

    private function getColumn(?string $column, string $key = null): mixed
    {
        $key = $key ? $column . '.' . $key : $column;

        return data_get($this->columns, $key);
    }

    private function setSetups(): Collection
    {
        $setups = collect($this->setup());
        $setups = $setups->keyBy(function (mixed $item, int $key) {
            return get_class($item);
        });

        return $setups;
    }

    private function getSearchableColumns(): Collection
    {
        return $this->columns->filter(function (mixed $value, string $key) {
                return $value['searchable'];
            });
    }

    private function getSortableColumns(): Collection
    {
        return $this->columns->filter(function (mixed $value, string $key) {
                return $value['sortable'];
            });
    }

    private function getSortedColumn(): ?array
    {
        $sorted = $this->getSortableColumns()->keyBy('name')->get($this->orderColumn);

        if (is_null($sorted) && $this->defaultOrderColumn) {
            $sorted = [
                'sortable' => [$this->defaultOrderColumn]
            ];
        }

        return $sorted;
    }

    private function getFilterableColumns(): Collection
    {
        return $this->columns->filter(function (mixed $value, string $key) {
                return $value['filterable'];
            });
    }

    private function setFilterableColumn(Column $column): bool|object
    {
        if (is_callable($column->filterable)) {
            return call_user_func($column->filterable);
        }

        return false;
    }

    private function getSaveableRequest(): array
    {
        $filtered = array_filter(request()->all(), function ($value, $key) {
            return !in_array(array_search($key, $this->getRequestMap()), $this->saveStateFilter);
        }, ARRAY_FILTER_USE_BOTH);

        return collect($filtered)
            ->filter()
            ->mapWithKeys(function (mixed $item, mixed $key) {
                $flattened = [];

                if (is_array($item)) {
                    foreach ($item as $subKey => $subItem) {
                        $flattened["{$key}[{$subKey}]"] = $subItem;

                        if (is_array($subItem)) {
                            foreach ($subItem as $subItemKey => $subItemValue) {
                                $flattened["{$key}[{$subKey}][{$subItemKey}]"] = $subItemValue;
                            }
                        }
                    }
                }

                return [
                    $key => $item,
                    ...$flattened
                ];
            })
            ->filter(function (mixed $value, mixed $key) {
                return !is_array($value);
            })
            ->all();
    }

    private function setOrderColumn(): ?string
    {
        if (request()->has($this->getRequestMap('order_column'))) {
            return $this->request('order_column');
        }

        return $this->orderColumn;
    }

    private function setPerPage(): int
    {
        $perPage = is_numeric($this->request('per_page')) ? $this->request('per_page') : null;
        $perPage = array_key_exists($perPage, $this->perPageOptions) ? $perPage : null;

        return $perPage ?? $this->perPage ?? $this->config('per_page');
    }

    private function validateOrderDirection(?string $direction): string
    {
        return match ($direction) {
            'asc' => 'asc',
            'desc' => 'desc',
            default => ''
        };
    }

    private function setOrderDirection(): string
    {
        $direction = $this->request('order_direction');

        if (!request()->has($this->getRequestMap('order_direction'))) {
            $direction = $this->orderDirection;
        }

        return $this->validateOrderDirection($direction);
    }

    private function getDataSourceType(): string
    {
        if ($this->dataSource instanceof Eloquent) {
            return 'eloquent';
        }

        if ($this->dataSource instanceof QueryBuilder) {
            return 'query';
        }

        if ($this->dataSource instanceof Collection) {
            return 'collection';
        }

        throw new \Exception('The data source must be an instance of Eloquent, QueryBuilder or Collection.');
    }

    private function view(string $view): string
    {
        return 'datatable::' . $this->getTemplate() . '.' . $view;
    }

    private function getSearchKeywords(): array
    {
        $keywords = $this->deepSearch 
            ? explode(' ', $this->request('search') ?? '') 
            : [$this->request('search')];
        $keywords = array_filter($keywords);

        if (count($keywords) > 1) {
            array_unshift($keywords, $this->request('search'));
        }

        return $keywords;
    }

    private function queryBuilder(): Eloquent|QueryBuilder
    {
        $sortable = $this->getSortedColumn();
        $requestFilters = $this->request('filters');
        
        return $this->dataSource
            ->when($this->request('search'), function ($query) {
                $query->where(function ($query) {
                    foreach ($this->getSearchableColumns()->pluck('searchable')->flatten()->all() as $column) {
                        if (is_callable($column)) {
                            call_user_func($column, $query, $this->request('search'));
                        } else {
                            foreach ($this->getSearchKeywords() as $keyword) {
                                $query->orWhere($column, 'like', '%'. $keyword .'%');
                            }
                        }
                    }
                });
            })
            ->when($requestFilters, function ($query) use ($requestFilters) {
                foreach ($this->getFilterableColumns()->all() as $column) {
                    $value = data_get($requestFilters, $column['alias']);

                    if (!is_bool($column['filterable']) && !is_null($value)) {
                        $column['filterable']->getFilter($column['name'], $value, $query);
                    }
                }  
            })
            ->when($sortable && !empty($this->orderDirection), function ($query) use ($sortable) {
                if (is_callable($sortable['sortable'])) {
                    call_user_func($sortable['sortable'], $query, $this->orderDirection);
                } else if (is_array($sortable['sortable'])) {
                    foreach ($sortable['sortable'] as $column) {
                        $query->orderBy($column, $this->orderDirection);
                    }
                }
            });
    }

    private function collectionBuilder(): Collection
    {
        $sortable = $this->getSortedColumn();
        $requestFilters = $this->request('filters');

        return $this->dataSource
            ->when($this->request('search'), function (Collection $collection) {
                $columns = $this->getSearchableColumns()->pluck('searchable')->flatten();
                
                foreach ($columns->all() as $column) {
                    if (is_callable($column)) {
                        return call_user_func($column, $collection, $this->request('search'));
                    }
                }

                return $collection->filter(function ($item) use ($columns) {
                    $truthy = [];

                    foreach ($columns->all() as $column) {
                        $value = strtolower($item[$column]);
                        $keywordTruthy = [];

                        foreach ($this->getSearchKeywords() as $keyword) {
                            $keywordTruthy[$keyword] = strpos($value, strtolower($keyword)) !== false;
                        }

                        $truthy[$column] = array_search(true, $keywordTruthy, true) !== false ? true : false;
                    }

                    return array_search(true, $truthy, true) !== false ? true : false;
                });
            })
            ->when($requestFilters, function (Collection $collection) use ($requestFilters) {
                foreach ($this->getFilterableColumns()->all() as $column) {
                    $value = data_get($requestFilters, $column['alias']);

                    if (!is_bool($column['filterable']) && !is_null($value)) {
                        $collection = $column['filterable']->getFilter($column['name'], $value, $collection);
                    }
                } 

                return $collection;
            })
            ->when($sortable && !empty($this->orderDirection), function (Collection $collection) use ($sortable) {
                if (is_callable($sortable['sortable'])) {
                    return call_user_func($sortable['sortable'], $collection, $this->orderDirection);
                } else if (is_array($sortable['sortable'])) {
                    $columns = [];

                    foreach ($sortable['sortable'] as $column) {
                        $columns[] = [$column, $this->orderDirection];
                    }

                    return $collection->sortBy($columns);
                }
            });
    }

    private function paginator(): Paginator
    {
        $this->dataSource = $this->dataSource();

        if (in_array($this->getDataSourceType(), ['eloquent', 'query'])) {
            return !$this->showTotalItems()
                ? $this->queryBuilder()
                    ->simplePaginate(perPage: $this->perPage, pageName: $this->getRequestMap('page'))
                : $this->queryBuilder()
                    ->paginate(perPage: $this->perPage, pageName: $this->getRequestMap('page'));
        }

        return !$this->showTotalItems()
            ? $this->collectionBuilder()
                ->simplePaginate(perPage: $this->perPage, pageName: $this->getRequestMap('page'))
            : $this->collectionBuilder()
                ->paginate(perPage: $this->perPage, pageName: $this->getRequestMap('page'));
    }

    private function transformer(Paginator $paginator): array
    {
        $data = [];

        for ($i = 0; $i < count($paginator->items()); $i++) { 
            $items = [];
            $index = $paginator->firstItem() + $i;
            $model = $paginator[$i];

            foreach ($this->columns as $key => $column) {
                $value = data_get($model, $column['name']);

                if (is_callable($column['edit'])) {
                    $callbackValue = call_user_func($column['edit'], $value, $model, $index);
                    $items[$key]['value'] = $callbackValue instanceof View ? $callbackValue->render() : $callbackValue;
                } else {
                    $items[$key]['value'] = $column['index'] ? $index : $value;
                }

                $items[$key]['attributes'] = is_callable($column['attributes'])
                    ? call_user_func($column['attributes'], $value, $model, $index)
                    : $column['attributes'];
     
                $items[$key]['checkbox']['attributes'] = is_callable($column['checkbox']['attributes'])
                    ? call_user_func($column['checkbox']['attributes'], $value, $model, $index)
                    : $column['checkbox']['attributes'];

                $items[$key]['checkbox']['enabled'] = $column['checkbox']['enabled'];
                $items[$key]['responsive'] = $column['responsive'];
                $items[$key]['alignment'] = $column['alignment'];
            }

            $data[$index] = [
                'model' => $model,
                'columns' => $items
            ];
        }

        return $data;
    }
}
