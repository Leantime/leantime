@props([
    'type' => '', //status, milestone, effort, priority, user, sprint
    'parentId' => '',
    'bgColor' => '',
    'quickaddOption' => false,
    'quickaddPostUrl' => '',
    'labelText'=> '',
    'selectedKey' => '',
    'options' => [],
    'extraClass' => '',
])

@php

@endphp

<div {{ $attributes->merge([ 'class' => '' ]) }}>
    <div class="dropdown ticketDropdown {{ $type }}Dropdown colorized show px-2 py-0.5 ml-1.5 {{ $extraClass }}">
        <a  
            href="javascript:void(0);"
            style="{{ $bgColor }}" 
            role="button" 
            id="{{ $type }}DropdownMenuLink{{ $parentId }}" 
            data-toggle="dropdown" 
            aria-haspopup="true" 
            aria-expanded="false"
            class="dropdown-toggle"
        >
            <span class="text">
                @if(isset($options[$selectedKey]))
                    @if(is_array($options[$selectedKey]))
                        {{ $options[$selectedKey]['name'] }}
                    @else
                        {{ $options[$selectedKey] }}
                    @endif
                @elseif(!empty($labelText))
                    {!! $labelText !!}
                @else
                    {{ __("label.".$type."_unknown") }}
                @endif
            </span>
            &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
        </a>

        <ul class="dropdown-menu" 
            aria-labelledby="{{ $type }}DropdownMenuLink{{ $parentId }}"
        >
            {{ $slot }}

            @if($quickaddOption)
                <x-global::forms.chip.quick-add
                    :type="$type"
                    :parentId="$parentId"
                    :postUrl="$quickaddPostUrl"
                />
            @endif
        </ul>
        <input type="hidden" name="{{ $type }}Field" value="{{ $selectedKey }}" id="dropdownPillInput-{{ $parentId }}-{{ $type }}" />
    </div>
</div>

