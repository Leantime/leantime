@props([
    'header',
    'body',
    'footer',
    'extraClass' => ''
])

<table {{ $attributes->merge(['class' => 'table table-bordered min-w-full bg-white border-separate border-spacing-0 '.$extraClass ]) }}>
    @if(isset($header))
        <thead class="bg-gray-50">
            {{ $header }}
        </thead>
    @endif
    
    <tbody class="bg-white divide-y divide-gray-200">
        {{ $body }}
    </tbody>
    
    @if(isset($footer))
        <tfoot class="bg-gray-50">
            {{ $footer }}
        </tfoot>
    @endif
</table>
