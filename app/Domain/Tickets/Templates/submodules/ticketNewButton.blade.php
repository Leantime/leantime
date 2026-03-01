@php
    $newField = $tpl->get('newField');
@endphp

@if($login::userIsAtLeast($roles::$editor) && !empty($newField))
    <x-globals::actions.dropdown-menu variant="button" :label="__('links.new_with_icon')" content-role="primary">
        @foreach($newField as $option)
            <li>
                <a href="{{ $option['url'] ?? '' }}"
                   class="{{ $option['class'] ?? '' }}">
                    {{ !empty($option['text']) ? __($option['text']) : '' }}
                </a>
            </li>
        @endforeach
    </x-globals::actions.dropdown-menu>
@endif
