@props([
    'header' => null,
    'body' => null,
    'footer' => null,
])

{{--
@if (! empty($buttons = data_get($tableConfig, 'layout.*.buttons.*', [])))
    <div class="flex items-center justify-end mb-4">
        @foreach ($buttons as $button)
            <x-global::forms.button
                scale="sm"
                onclick="window['{!! $id !!}'].button('.buttons-{{ $button['extend'] }}').trigger()"
                class="mr-2"
            >{!! $button['text'] !!}</x-global::forms.button>
        @endforeach
    </div>
@endif
--}}

<table {{ $attributes->merge([
    'class' => 'table table-pin-rows !mb-0 min-w-full bg-white table-bordered !border-t-0 !border-b-0 !border-l-0 !border-r-0',
    'id' => $id,
]) }}>
    @if($header->isNotEmpty())
        <thead {{ $header->attributes->merge(['class' => 'bg-gray-50 [&_th:first-child]:!border-l-0']) }}>
            {{ $header }}
        </thead>
    @endif

    @if($body->isNotEmpty())
        <tbody {{ $body->attributes->merge(['class' => 'bg-white [&_td:first-child]:!border-l-0']) }}>
            {{ $body }}
        </tbody>
    @endif

    @if($footer->isNotEmpty())
        <tfoot {{ $footer->attributes->merge(['class' => 'bg-gray-50 [&_td:first-child]:!border-l-0']) }}>
            {{ $footer }}
        </tfoot>
    @endif
</table>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', event => {
            window['{!! $id !!}'] = window.leantime.tableModule.initDataTable(
                '#{!! $id !!}',
                @js($tableConfig)
            );
        });
    </script>
@endpush
