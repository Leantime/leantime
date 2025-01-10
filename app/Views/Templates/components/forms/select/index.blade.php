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

    'dropdownPosition' => 'left',

    //Variation options
    'variant' => 'single', //single, multiple, tags, chip
    'search' => 'false',
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
            $contentRoleClass = '';
            break;
        case 'tertiary':
        case 'ghost':
            $contentRoleClass = '';
            break;
        case 'link':
            $contentRoleClass = '';
            break;
        default:
            $contentRoleClass = '';
    }
    $selectClassArray = [
        'select-'.$formHash,
        'select',
        $contentRoleClass,
        $selectVariant,
        $sizeClass,
        $stateClass,
        "w-full",
        "anchor-".$dropdownPosition
        // "max-w-xs",

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
            <div class="select-loading-state hidden">
                <x-global::elements.loadingText
                    type="line"
                    count="1"
                    class="w-full min-w-[100px]" />
            </div>

            @if($leadingVisual)
                <x-global::elements.leadingVisual>
                    {{ $leadingVisual }}
                </x-global::elements.leadingVisual>
            @endif

            <div class="{{ (!empty($leadingVisual) ? 'ml-lg' : '') }}">
                <select
                    {{$attributes->merge([
                        'class' => "opacity-0 ".$selectClassBuilder,
                        'data-component' => 'select',
                        'data-component-config' => '{"search": '.$search.'}'
                    ])}}
                    name="{{ $name }}"
                    {{ $state === 'disabled' ? 'disabled' : '' }}
                    {{ $variant === 'multiple' || $variant === 'tags' ? 'multiple' : '' }}>
                    {{ $slot }}
                </select>
            </div>
        </div>

    </div>

    @if($validationText)
        <x-slot:validation-text> {!! $validationText !!}</x-slot:validation-text>
    @endif

</x-global::forms.field-row>
