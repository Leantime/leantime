<?php

namespace Leantime\Views\Components\Elements;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class Table extends Component
{
    public array $tableConfig = [];

    public function __construct(
        public string $id = '',
        public bool $colvis = true,
        public bool $allowExport = true,
    ) {
        if (empty($this->id)) {
            $this->id = 'table-' . uniqid();
        }

        $this->tableConfig['language'] = [
            'decimal' => __("datatables.decimal"),
            'emptyTable' => __("datatables.emptyTable"),
            'info' => __("datatables.info"),
            'infoEmpty' => __("datatables.infoEmpty"),
            'infoFiltered' => __("datatables.infoFiltered"),
            'infoPostFix' => __("datatables.infoPostFix"),
            'thousands' => __("datatables.thousands"),
            'lengthMenu' => __("datatables.lengthMenu"),
            'loadingRecords' => __("datatables.loadingRecords"),
            'processing' => __("datatables.processing"),
            'search' => __("datatables.search"),
            'zeroRecords' => __("datatables.zeroRecords"),
            'paginate' => [
                'first' => __("datatables.first"),
                'last' => __("datatables.last"),
                'next' => __("datatables.next"),
                'previous' => __("datatables.previous"),
            ],
            'aria' => [
                'sortAscending' => __("datatables.sortAscending"),
                'sortDescending' => __("datatables.sortDescending"),
            ],
            'buttons' => [
                'colvis' => __("datatables.buttons.colvis"),
                'csv' => __("datatables.buttons.download")
            ],
        ];
        $this->tableConfig['colReorder'] = true;
        $this->tableConfig['responsive'] = true;
        $this->tableConfig['saveState'] = true;
        $this->tableConfig['searching'] = false;

        if ($this->colvis) {
            data_set(
                $this->tableConfig,
                'layout.topEnd.buttons',
                array_merge(data_get($this->tableConfig, 'layout.topEnd.buttons', []), ['colvis'])
            );
        }

        if ($this->allowExport) {
            data_set(
                $this->tableConfig,
                'layout.topEnd.buttons',
                array_merge(data_get($this->tableConfig, 'layout.topEnd.buttons', []), ['csv'])
            );
        }

        // Hide buttons and let the view handle the buttons
        $this->tableConfig = collect($this->tableConfig)
        ->dot()
        ->map(function ($value, $key) {
            if (
                ! str_contains($key, '.buttons.')
                || ! str_starts_with($key, 'layout.')
            ) {
                return $value;
            }

            $button = Str::afterLast($value, '.');

            return [
                'extend' => $button,
                'className' => 'btn btn-primary border-primary hover:bg-primary btn-sm',
                // 'className' => 'hidden',
                // 'text' => $this->tableConfig['language']['buttons'][$button],
            ];
        })
        ->undot()
        ->all();
    }

    public function render()
    {
        return view('global::components.elements.table.index');
    }
}
