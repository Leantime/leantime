@if ($login::userIsAtLeast($roles::$editor) && !empty($newField))
    <x-globals::actions.dropdown>
        @foreach ($newField as $option)
        <x-globals::actions.dropdown.item href="{{ !empty($option['url']) ? $option['url'] : '' }}">
                {!! __($option['text']) !!}
        </x-globals::actions.dropdown.item>
        @endforeach
    </x-globals::actions.dropdown>
@endif
