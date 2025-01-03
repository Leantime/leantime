@if ($login::userIsAtLeast($roles::$editor) && !empty($newField))
    <x-global::actions.dropdown scale="sm">
        <x-slot:label-text>
                {!! __("links.new_with_icon") !!} <span class="fa fa-caret-down"></span>
        </x-slot:label-text>
        <x-slot:menu>
            @foreach ($newField as $option)
                <x-global::actions.dropdown.item href="{{ !empty($option['url']) ? $option['url'] : '' }}">
                    {!! __($option['text'])  !!}
                </x-global::actions.dropdown.item>
            @endforeach
        </x-slot:menu>
    </x-global::actions.dropdown>
@endif

