<?php

namespace VariableSign\DataTable;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder as Eloquent;
use Illuminate\Database\Query\Builder as QueryBuilder;

abstract class DataTable
{
    protected string $tableName = 'datatable';

    protected ?string $queryStringPrefix = null;

    protected ?string $orderColumn = null;

    protected string $orderDirection = 'asc';

    protected ?int $perPage = null;

    protected ?array $perPageOptions = null;

    protected ?int $onEachSide = null;

    protected bool $skipTotal = false;

    protected ?bool $deepSearch = null;

    protected ?bool $saveState = null;

    protected ?array $saveStateFilter = null;

    protected ?string $template = null;

    protected bool $showHeader = true;

    protected bool $showPageOptions = true;

    protected bool $showInfo = true;

    protected bool $showPagination = true;

    protected ?string $searchPlaceholder = null;

    private Collection $columns;

    private Collection $setups;

    private array $options;

    private string $table;

    private ?string $defaultOrderColumn = null;

    private array $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
        $this->perPageOptions = $this->perPageOptions ?? $this->config('per_page_options');
        $this->defaultOrderColumn = $this->orderColumn;
        $this->columns = $this->setColumns();
        $this->options = $this->setOptions();
    }

    public function render(): ?string
    {
        $build = '';
        $attributes = collect($this->config('attributes'));
        $attributes = $attributes->map(function (string $item, string $key) {
            return __($item, [
                'url' => route($this->config('route.name'), [
                    $this->getFullTableName(),
                    'data' => $this->getData()
                ]),
                'options' => json_encode($this->options)
            ]);
        });

        foreach ($attributes as $key => $value) {
            $build .= is_array(json_decode($value, true))
                ? $key . "='" . $value . "' " 
                : $key . '="' . $value . '" ';
        }

        return trim($build);
    }

    public function getFullTableName(): string
    {
        return str(get_class($this))
            ->replaceFirst('App\\' . $this->config('directory') . '\\', '')
            ->explode('\\')
            ->transform(function (string $value, int $key) {
                return str($value)->kebab()->toString();
            })
            ->join('.');
    }

    public function getOption(string $key = null, mixed $default = null): mixed
    {
        return $key ? data_get($this->options, $key, $default) : $this->options;
    }

    public function getData(?string $key = null, mixed $default = null): mixed
    {
        return data_get($this->data, $key, $default) ?? $this->data;
    }

    public function request(string $key): null|string|array
    {
        $request = data_get($this->getRequestMap(), $key);

        return is_null($request) ? null : request()->get($request);
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

    private function config(?string $key = null, mixed $default = null): mixed
    {
        $key = $key ? 'datatable.' . $key : 'datatable';

        return config($key, $default);
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

    private function setFilterableColumn(Column $column): bool|object
    {
        if (is_callable($column->filterable)) {
            return call_user_func($column->filterable);
        }

        return false;
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

    private function getRequestMap(?string $key = null): string|array|null
    {
        $map = $this->config('request_map');

        if ($this->queryStringPrefix) {
            $map = array_map(function ($item) {
                return $this->queryStringPrefix . '_' . $item;
            }, $map);
        }

        return $key ? data_get($map, $key) : $map;
    }

    private function getDataSourceType(): ?string
    {
        if ($this->dataSource() instanceof Eloquent) {
            return 'eloquent';
        }

        if ($this->dataSource() instanceof QueryBuilder) {
            return 'queryBuilder';
        }

        if ($this->dataSource() instanceof Collection) {
            return 'collection';
        }

        return null;
    }

    private function setOptions(): array
    {
        $this->orderColumn = $this->getColumn($this->setOrderColumn(), 'name');
        $this->orderDirection = $this->setOrderDirection();

        return [
            // 'template' => $this->template ?? $this->config('template'),
            'table_name' => $this->tableName,
            // 'table_id' => str($this->table)->replace('.', '-')->toString(),
            // 'data_source' => $this->getDataSource(),
            // 'skip_total' => $this->skipTotal,
            // 'deep_search' => $this->deepSearch ?? $this->config('deep_search'),
            // 'order_column' => $this->orderColumn,
            // 'order_direction' => $this->orderDirection,
            // 'per_page' => $this->setPerPage(),
            // 'filtered' => $this->getActiveFilterCount(),
            // 'auto_update_on_filter' => $this->autoUpdateOnFilter ?? $this->config('auto_update_on_filter'),
            // 'per_page_options' => $this->perPageOptions,
            // 'storage' => $this->storage ?? $this->config('storage'),
            // 'save_state' => $this->saveState ?? $this->config('save_state'),
            // 'save_state_filter' => $this->saveStateFilter ?? $this->config('save_state_filter'),
            // 'query_string_prefix' => $this->queryStringPrefix,
            // 'auto_update' => $this->autoUpdate,
            // 'auto_update_interval' => $this->autoUpdateInterval,
            // 'on_each_side' => $this->onEachSide ?? $this->config('on_each_side'),
            // 'search_placeholder' => $this->getSearchPlaceholder($this->searchPlaceholder),
            // 'url' => request()->fullUrl(),
            // 'request' => [
            //     'query' => request()->all(),
            //     'save' => $this->getSaveableRequest(),
            //     'map' =>  $this->getRequestMap()
            // ],
            // 'data' => $this->data,
            // 'show_header' => $this->showHeader,
            // 'show_info' => $this->showInfo,
            // 'show_page_options' => $this->showPageOptions,
            // 'show_pagination' => $this->showPagination,
            // 'attributes' => $this->config('attributes')
        ];
    }
}
