@props([
    'value' => '',
    'selected' => false
])

<option value="{{ $value }}" {{ $selected ? 'selected' : '' }}>
    {{--
    Option tags are not allowed to contain html and it will not be rendered by the browser
    We are going to escape the html if there is any and have our library decode the html
    --}}
    {!! htmlentities($slot, ENT_NOQUOTES, "UTF-8", false) !!}
</option>
