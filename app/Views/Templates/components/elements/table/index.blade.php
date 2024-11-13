@props([
    'header',
    'body',
    'footer',
    'tableId' => 'table-' . uniqid(),
])

<table {{ $attributes->merge([
    'class' => 'table table-bordered min-w-full bg-white border-separate border-spacing-0',
    'id' => $tableId,
]) }}>
    @if($header->isNotEmpty())
        <thead {{ $header->attributes->merge(['class' => 'bg-gray-50']) }}>
            {{ $header }}
        </thead>
    @endif

    @if($body->isNotEmpty())
        <tbody {{ $body->attributes->merge(['class' => 'bg-white divide-y divide-gray-200']) }}>
            {{ $body }}
        </tbody>
    @endif

    @if($footer->isNotEmpty())
        <tfoot {{ $footer->attributes->merge(['class' => 'bg-gray-50']) }}>
            {{ $footer }}
        </tfoot>
    @endif
</table>

@push('scripts')
<script>
window.addEventListener(
    'load',
    (event) => window['{!! $tableId !!}'] = window.leantime.tableModule.initDataTable('#{!! $tableId !!}')
);

</script>
@endpush
