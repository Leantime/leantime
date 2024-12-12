@props([
    'type' => '', //status, milestone, effort, priority, user, sprint
    'parentId' => '',
    'selectedClass' => '', 
    'selectedKey' => '',
    'bgColor' => '',
    'options' => [],
    'extraClass' => '',
    'linkStyle' => '',
    'submit' => "false",
    'labelText'=> '',
    'addOption' => false,
])

@php

@endphp

<div {{ $attributes->merge([ 'class' => '' ]) }}>
    {{-- <a style="{{ $linkStyle }}" class="dropdown-toggle inline-block {{ $type }} {{ $selectedClass }} {{ $type }}-bg-{{ $selectedClass }}" href="javascript:void(0);" role="button" id="{{ $type }}DropdownMenuLink{{ $parentId }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> --}}
        {{-- class="dropdown-toggle inline-block" --}}
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

            @if($addOption)
                <x-chip::quickAdd
                    :type="$type"
                    :parentId="$parentId"
                    :postUrl="{{ BASE_URL }}/tickets/editMilestone"
                    :redirectUrl="{{ BASE_URL }}/tickets/showAll"
                />
            @endif
        </ul>
        <input type="hidden" name="{{ $type }}Field" value="{{ $selectedKey }}" id="dropdownPillInput-{{ $parentId }}-{{ $type }}" />
    </div>
    {{-- </a> --}}
</div>


{{-- <script>

    jQuery.fn.removeClassStartingWith = function (filter) {
        jQuery(this).removeClass(function (index, className) {
            return (className.match(new RegExp("\\S*" + filter + "\\S*", 'g')) || []).join(' ')
        });
        return this;
    };
    // key, bgClass, type, parentId
    function pillSelect(element) {
        console.log("I am here");
        // Get the data-value which contains key, background color
        const dataValue = element.getAttribute('data-value');
        console.log("dataValue is: ", dataValue);
        
        // Split the data-value into its components
        const [key, bgClass, bgColor] = dataValue.split('_');
        console.log("key, bgClass and bgColor are: ", key, bgClass, bgColor);
        
        // Get the parent dropdown wrapper
        const parentWrapper = element.closest('.dropdown');
        console.log("parentWrapper is: ", parentWrapper);


        // Update hidden input
        const hiddenInput = parentWrapper.querySelector('input[type="hidden"]');
        console.log("hiddenInput[PRE] is: ", hiddenInput);

        if (hiddenInput) {
            hiddenInput.value = key;
        }

        console.log("hiddenInput[POST] is: ", hiddenInput);

        // Update dropdown toggle text
        const dropdownToggle = parentWrapper.querySelector('a[data-toggle="dropdown"]');
        if (dropdownToggle) {
            const textSpan = dropdownToggle.querySelector('.text');
            if (textSpan) {
                textSpan.textContent = element.textContent;
            }

            // Update background style if needed
            dropdownToggle.style.backgroundColor = bgColor;
        }
        console.log("dropdownToggle[POST] is: ", dropdownToggle);

    }

    // Add click event listener to all dropdown items
    document.addEventListener('DOMContentLoaded', function() {
        const dropdownItems = document.querySelectorAll('.dropdown-menu li a');
        dropdownItems.forEach(item => {
            item.addEventListener('click', function() {
                pillSelect(this);

                // Close dropdown (if using Bootstrap)
                const dropdown = this.closest('.dropdown');
                const dropdownToggle = dropdown.querySelector('[data-toggle="dropdown"]');
                if (dropdownToggle) {
                    dropdownToggle.dropdown('toggle');
                }
            });
        });
    });

</script> --}}


