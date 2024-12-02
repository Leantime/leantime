@props([
    //Basic Definition
    'contentRole' => '', //default, primary, secondary, tertiary (ghost), accent, link
    'state' => '', //default, info, warning, danger, success,
    'scale' => '', //xs, sm, md, lg, xl

    //labels & content
    'labelPosition' => 'top',
    'labelText' => '',
    'helpText' => '',
    'caption' => '',
    'leadingVisual' => '',
    'validationText' => '',
    'validationState' => '',

    //Variation options
    'variant' => 'single', //single, multiple, tags, chip
    'search' => 'true',
    'addChoices' => 'false',
    'autocompleteTags' => false,
    'formHash' => md5(CURRENT_URL."selectChoices".mt_rand(0,100)),
    'value' => '',
    'maxItemCount' => '',
    'name' => '',
])

@php
    $sizeClass = $scale && $scale != 'md' ? 'select-'.$scale : '';
    $stateClass = $state && $state != 'disabled' ? 'select-'.$state : '';
    $validationClass = $validationState ? 'text-yellow-500' : '';

    $selectVariant = '';
    if($variant == 'chip'){
        $selectVariant = "select-chip";
    }

    switch($contentRole){
        case 'secondary':
            $contentRoleClass = 'select-bordered';
            break;
        case 'tertiary':
        case 'ghost':
            $contentRoleClass = '';
            break;
        case 'link':
            $contentRoleClass = '';
            break;
        default:
            $contentRoleClass = 'select-bordered';
    }
    $selectClassArray = [
        'select-'.$formHash,
        'select',
        $contentRoleClass,
        $selectVariant,
        $sizeClass,
        $stateClass,
        "w-full",
        // "max-w-xs",
        ($leadingVisual ? 'pl-10' : '')
    ];

    //Clean up array and implode for js
    $selectClassBuilder = implode(" ", array_filter(array_map('trim', $selectClassArray)));

@endphp

<x-global::forms.field-row :label-position="$labelPosition">

    @if($labelText)
        <x-slot:label-text> {!! $labelText !!}</x-slot:label-text>
    @endif

    @if($helpText)
        <x-slot:help-text> {!! $helpText !!}</x-slot:help-text>
    @endif

    @if($caption)
        <span class="label-text">{{ $caption }}</span>
    @endif

    <div {{$attributes->merge(['class' => ($variant === 'tags' ? 'tags inline-block w-full' : '')])}} >
        <div class="flex flex-row">
            @if($leadingVisual)
                <x-global::elements.leadingVisual>
                    {{ $leadingVisual }}
                </x-global::elements.leadingVisual>
            @endif

            <select
                {{$attributes->merge(['class' => $selectClassBuilder ])}}
                name="{{ $name }}"
                {{ $state === 'disabled' ? 'disabled' : '' }}
                {{ $variant === 'multiple' || $variant === 'tags' ? 'multiple' : '' }}>
                {{ $slot }}
            </select>
        </div>

    </div>

    @if($validationText)
        <x-slot:validation-text> {!! $validationText !!}</x-slot:validation-text>
    @endif


</x-global::forms.field-row>

<script>
    jQuery(function () {

        // Initialize select elements when the page is ready
        initSelect();

        // Re-initialize select elements when HTMX loads new content
        htmx.onLoad(function () {
            initSelect();
        });

        function initSelect() {
            @if ($variant === 'tags')
                leantime.selects.initTags(
                    '.select-{{ $formHash }}',
                    {{ $search }},
                    {{ (!$autocompleteTags) ? 'false' : 'true' }},
                    '{{ $selectClassBuilder }}',
                    {{ $maxItemCount }}
                );
            @else
                leantime.selects.initSelect(
                    '.select-{{ $formHash }}',
                    {{ $search }},
                    '{{ $selectClassBuilder }}'
                );
            @endif
        }
    });
</script>

