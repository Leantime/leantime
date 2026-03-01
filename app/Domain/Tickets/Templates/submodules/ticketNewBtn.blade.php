@php
    $newField = $tpl->get('newField');
@endphp

@if($login::userIsAtLeast($roles::$editor) && !empty($newField))
    <x-globals::actions.dropdown-menu variant="button" :label="__('links.new_with_icon')" content-role="primary" class="pull-left" style="margin-right:5px;">
        @foreach($newField as $option)
            <li>
                <a href="{{ $option['url'] ?? '' }}"
                   class="{{ $option['class'] ?? '' }}">
                    @if(!empty($option['icon']))<x-global::elements.icon :name="$option['icon']" /> @endif{{ !empty($option['text']) ? __($option['text']) : '' }}
                </a>
            </li>
        @endforeach
    </x-globals::actions.dropdown-menu>
@endif
