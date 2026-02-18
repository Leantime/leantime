@props([
    'type' => '',
    'selectedClass' => '',
    'selectedKey' => '',
    'parentId' => '',
    'options' => [],
    'extraClass' => '',
    'linkStyle' => '',
    'submit' => "false",
    'colorized' => false,
    'noBg' => false,
    'align' => 'end',
    'headerLabel' => '',
    'hxPost' => '',
    'hxSwap' => 'none',
    'hxIndicator' => '',
])

<div {{ $attributes->merge(['class' => '']) }}>
    <div class="tw:dropdown tw:dropdown-{{ $align }} ticketDropdown {{ $type }}Dropdown {{ $colorized ? 'colorized' : '' }} {{ $noBg ? 'noBg' : '' }} {{ $extraClass }}">
        <div tabindex="0" role="button" style="{{ $linkStyle }}" class="dropdown-toggle f-left {{ $type }} {{ $selectedClass }}" id="{{ $type }}DropdownMenuLink{{ $parentId }}" aria-haspopup="true" aria-expanded="false">
            <span class="text">
                @if(isset($options[$selectedKey]))
                    @if(is_array($options[$selectedKey]))
                        {{ $options[$selectedKey]['name'] }}
                    @else
                        {{ $options[$selectedKey] }}
                    @endif
                @else
                    {{ __("label.".$type."_unknown") }}
                @endif
            </span>
            &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
        </div>
        <ul tabindex="0" class="dropdown-menu tw:dropdown-content tw:menu tw:bg-base-100 tw:rounded-box tw:z-50 tw:min-w-52 tw:p-2 tw:shadow-sm" aria-labelledby="{{ $type }}DropdownMenuLink{{ $parentId }}">
            <li class="nav-header border">{{ $headerLabel ?: __("label.select_".$type) }}</li>
            @foreach ($options as $key => $value)
                @php
                    $itemClass = is_array($value) ? ($value['class'] ?? '') : '';
                    $isColor = str_starts_with((string)$itemClass, '#');
                    $itemLabel = is_array($value) ? $value['name'] : $value;
                @endphp
                <li class='dropdown-item'>
                    <a href='javascript:void(0);'
                       class='dropdownPillLink {{ $type }}-bg-{{ $key }} {{ $isColor ? "" : $itemClass }}'
                       @if($isColor) style="background-color: {{ $itemClass }}" @endif
                       id='{{ $type }}Change{{ $parentId }}{{ $key }}'
                       onclick="jQuery('#dropdownPill-{{ $parentId }}-{{ $type }}').val('{{ $key }}'); @if($submit !== "false") document.querySelector('{{ $submit }}').submit(); @endif document.activeElement.blur();"
                       data-label='{{ $itemLabel }}'
                       data-value='{{ $parentId }}_{{ $key }}{{ $itemClass ? "_" . $itemClass : "" }}'
                       @if($hxPost)
                           hx-post="{{ $hxPost }}"
                           hx-swap="{{ $hxSwap }}"
                           hx-vals='{"id": "{{ $parentId }}", "{{ $type }}": "{{ $key }}"}'
                           @if($hxIndicator) hx-indicator="{{ $hxIndicator }}" @endif
                       @endif
                    >
                        {{ $itemLabel }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
    <input type="hidden" name="{{ $type }}" value="{{ $selectedKey }}" id="dropdownPill-{{ $parentId }}-{{ $type }}" />
</div>
