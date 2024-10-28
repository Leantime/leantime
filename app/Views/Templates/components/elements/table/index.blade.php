@props([
    'header',
    'body',
    'footer'
])

<table {{ $attributes->merge(['class' => 'table table-bordered']) }}>
    @if(isset($header))
        <thead>
            {{ $header }}
        </thead>
    @endif
    <tbody >
        {{ $body }}
    </tbody>
    @if(isset($footer))
        <tfoot>
            {{ $footer }}
        </tfoot>
    @endif
</table>
  
    
{{-- <div {{ $attributes->merge(['class' => 'overflow-x-auto']) }}> --}}
{{-- </div> --}}
