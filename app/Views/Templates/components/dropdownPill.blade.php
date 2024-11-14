@props([
    'type' => '',
    'selectedClass' => '',
    'selectedKey' => '',
    'parentId' => '',
    'options' => [],
    'extraClass' => '',
    'linkStyle'  => '',
    'submit' => "false"
])

<div {{ $attributes->merge([ 'class' => '' ]) }} >
    <div class="dropdown ticketDropdown {{ $type }}Dropdown show {{ $extraClass }}">
        <a style="{{ $linkStyle }}" class="dropdown-toggle f-left {{ $type }} {{ $selectedClass }}" href="javascript:void(0);" role="button" id="{{ $type }}DropdownMenuLink{{ $parentId }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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
        </a>
        <ul class="dropdown-menu" aria-labelledby="{{ $type  }}DropdownMenuLink{{ $parentId }}">
            <li class="nav-header border"> {{ __("label.select_".$type) }}</li>
            @foreach ($options as $key => $value)
               <li class='dropdown-item'>
                    <a href='javascript:void(0);' class="dropdownPillLink"

                       id='{{ $type }}Change{{ $parentId }}{{ $key }}'
                       onclick="jQuery('#dropdownPill-{{ $parentId }}-{{ $type }}').val('{{ $key }}'); @if($submit !== "false") document.querySelector('{{ $submit }}').submit(); @endif"
                         @if(is_array($value))
                           class='{{ $type }}-bg-{{ $key }} {{ $value["class"] }}'
                           data-label='{{ $value["name"] }}'
                           data-value='{{ $parentId }}_{{ $key }}_{{ $value["class"] }}'
                            >
                            {{ $value["name"] }}
                       @else
                           class='{{ $type }}-bg-{{ $key }}'
                           data-value='{{ $parentId  }}_{{ $key }}'
                            >
                            {{ $value }}
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
    <input type="hidden" name="{{ $type }}" value="{{ $selectedKey }}" id="dropdownPill-{{ $parentId }}-{{ $type }}" />
</div>
