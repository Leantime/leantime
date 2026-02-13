@props([
    'id' => null,
    'striped' => false,
])

<div style="overflow-x: auto;">
    <table {{ $attributes->merge([
        'id' => $id,
        'class' => 'tw:table tw:table-auto tw:w-full' . ($striped ? ' tw:table-zebra' : ''),
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
