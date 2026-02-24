@props([
    'id' => null,
    'striped' => false,
    'bordered' => false,
    'hover' => false,
    'compact' => false,
    'datatable' => false,
])

@php
    $tableClasses = 'tw:table tw:table-auto tw:w-full table'
        . ($striped ? ' tw:table-zebra table-striped' : '')
        . ($bordered ? ' table-bordered' : '')
        . ($hover ? ' table-hover' : '')
        . ($compact ? ' tw:table-xs table-condensed' : '')
        . ($datatable ? ' dataTable' : '');
@endphp

<div style="overflow-x: auto;">
    <table {{ $attributes->merge([
        'id' => $id,
        'class' => $tableClasses,
    ]) }}>
        @isset($head)
            <thead>
                {{ $head }}
            </thead>
        @endisset
        <tbody>
            {{ $slot }}
        </tbody>
        @isset($foot)
            <tfoot>
                {{ $foot }}
            </tfoot>
        @endisset
    </table>
</div>
