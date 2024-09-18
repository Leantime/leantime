@props([
    'labelText' => '',
    'labelRight' => '',
    'caption' => '',
    'leadingVisual' => '',
    'size' => '',
    'state' => '',
    'variant' => 'single', //single, multiple, tags
    'validationText' => '',
    'validationState' => '',
    'search' => 'true',
    'addChoices' => 'false',
    'autocompleteTags' => false,
    'formHash' => md5(CURRENT_URL."selectChoices".mt_rand(0,100)),
    'value' => ''
])

@php
    $sizeClass = $size && $size != 'md' ? 'select-'.$size : '';
    $stateClass = $state && $state != 'disabled' ? 'select-'.$state : '';
    $validationClass = $validationState ? 'text-yellow-500' : '';
@endphp


<div class='form-control relative w-full max-w-xs'>

    <x-global::forms.label-row>
        @if($labelText)
            <x-slot:label-text> {!! $labelText !!}</x-slot:label-text>
        @endif
        @if($labelRight)
            <x-slot:label-right> {!! $labelRight !!}</x-slot:label-right>
        @endif
    </x-global::forms.label-row>

    @if($caption)
        <span class="label-text">{{ $caption }}</span>
    @endif

    <div {{$attributes->merge(['class' => ($variant === 'tags' ? 'tags inline-block' : '')])}}>
        @if($leadingVisual)
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                {{ $leadingVisual }}>
            </span>
        @endif

        <select
            {{$attributes->merge(['class' => 'select select-bordered select-'.$formHash.' '.$sizeClass.' '.$stateClass.' w-full max-w-xs input-shadow '.($leadingVisual ? 'pl-10' : '')])}}
            {{ $state === 'disabled' ? 'disabled' : '' }}
            {{ $variant === 'multiple' || $variant === 'tags' ? 'multiple' : '' }}
        >
            {!! $slot !!}
        </select>

    </div>

    @if($validationText)
        <x-global::forms.label-row class="mt-1 transition-opacity duration-500 ease-in-out opacity-100">
            <x-slot:label-text-right class="{{ $validationClass }}"> {!! $validationText !!}</x-slot:label-text-right>
        </x-global::forms.label-row>
    @endif

</div>


<script>
    @if ($variant === 'tags')
        leantime.selects.initTags('.select-{{ $formHash }}',  {{ $search }}, {{ $autocompleteTags }});
    @else
        leantime.selects.initSelect('.select-{{ $formHash }}', {{ $search }});
    @endif
</script>




{{-- @props([
    "type" => "select-one", //select-one, select-multiple, tags
    "search" => 'true',
    "addChoices" => 'false',
    "style" => "standard", //standard, tags, pill
    "formHash" => md5(CURRENT_URL."selectChoices".mt_rand(0,100))
])

<div id="select-wrapper-{{ $formHash }}" hx-target="#select-wrapper-{{ $formHash }}" {{ $attributes->merge([ 'class' => "inline-block" ]) }}>
    <select class="select-{{ $formHash }}" {{ $type == "multiple" ? "multiple" : "" }}>
    </select>
</div>

<script>

    leantime.selects.initSelect('.select-{{ $formHash }}', [{{ $slot }}], {{ $search }});
</script> --}}
