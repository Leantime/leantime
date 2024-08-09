@props([
    "type" => "select-one", //select-one, select-multiple, tags
    "search" => 'true',
    "addChoices" => 'false',
    "style" => "standard", //standard, tags, pill
    "formHash" => md5(CURRENT_URL."selectChoices".mt_rand(0,100))
])

<div id="select-wrapper-{{ $formHash }}" hx-target="#select-wrapper-{{ $formHash }}" {{ $attributes->merge([ 'class' => "tw-inline-block" ]) }}>
    <select class="select-{{ $formHash }}" {{ $type == "multiple" ? "multiple" : "" }}>
    </select>
</div>

<script>
    leantime.selects.initSelect('.select-{{ $formHash }}', [{{ $slot }}], {{ $search }});
</script>
