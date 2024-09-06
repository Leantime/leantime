@props([
    "search" => 'true',
    'value' => '',
    'outlineVisible' => false,
    'name' => '',
    'autocompleteTags' => false,
    "formHash" => md5(CURRENT_URL."input-tags".mt_rand(0,100))
])

<div id="input-tags-wrapper-{{ $formHash }}" hx-target="#input-tags-wrapper-{{ $formHash }}" {{ $attributes->merge([ 'class' => "tags inline-block" ]) }}>
    <select multiple name="{{ $name }}" class="input-tags-field-{{ $formHash }} {{ $outlineVisible ? "show-border" : '' }}">
    </select>
</div>

<script>
    leantime.selects.initTags('.input-tags-field-{{ $formHash }}', '{{ $value }}',  {{ $search }}, {{ $autocompleteTags }});
</script>
