@props([
    'type' => '',
    'selectedClass' => '',
    'selectedKey' => '',
    'parentId' => '',
    'options' => []
])

<div {{ $attributes->merge([ 'class' => 'htmxWrapper' ]) }}>
    <div class="dropdown ticketDropdown {{ $type }}Dropdown show">
        <a class="dropdown-toggle f-left  label-default {{ $selectedClass }}" href="javascript:void(0);" role="button" id="{{ $type }}DropdownMenuLink{{ $parentId }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="text">
                {{ isset($options[$selectedKey]) ? $options[$selectedKey] : __("label.".$type."_unknown") }}
            </span>
            &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
        </a>
        <ul class="dropdown-menu" aria-labelledby="{{ $type  }}DropdownMenuLink{{ $parentId }}">
            <li class="nav-header border"> {{ __("label.select_".$type }}</li>
            @foreach ($options as $key => $value)
               <li class='dropdown-item'>
                    <a href='javascript:void(0);' class='{{ $type }}-bg-{{ $key }}' data-value='{{ $parentId  }}_{{ $key }}' id='{{ $type }}Change{{ $parentId }}{{ $key }}'>{{ $value }}</a>
                </li>
            @endforeach
        </ul>
    </div>
</div>
