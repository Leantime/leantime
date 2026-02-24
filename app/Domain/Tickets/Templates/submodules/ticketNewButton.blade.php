@php
    $newField = $tpl->get('newField');
@endphp

@if($login::userIsAtLeast($roles::$editor) && !empty($newField))
    <x-global::elements.button-dropdown :label="__('links.new_with_icon')" type="primary">
        @foreach($newField as $option)
            <li>
                <a href="{{ $option['url'] ?? '' }}"
                   class="{{ $option['class'] ?? '' }}">
                    {{ !empty($option['text']) ? __($option['text']) : '' }}
                </a>
            </li>
        @endforeach
    </x-global::elements.button-dropdown>
@endif
