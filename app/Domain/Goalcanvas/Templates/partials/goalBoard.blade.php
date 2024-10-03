@if (count($allCanvas) > 0)
<x-global::actions.dropdown label-text='All Goal Groups' contentRole="ghost" class="header-title-dropdown" position="bottom">
    <x-slot:menu>
        @if ($login::userIsAtLeast($roles::$editor))
            <x-global::actions.dropdown.item variant="link" href="#/goalcanvas/bigRock">
                {!! __('links.icon.create_new_board') !!}
            </x-global::actions.dropdown.item>
            <x-global::actions.dropdown.item variant="border" />
        @endif
        @foreach ($allCanvas as $canvasRow)
            <x-global::actions.dropdown.item variant="link"
                href="{{ BASE_URL }}/goalcanvas/showCanvas/{{ $canvasRow['id'] }}">
                {{ $tpl->escape($canvasRow['title']) }}
            </x-global::actions.dropdown.item>
        @endforeach
    </x-slot:menu>
</x-global::actions.dropdown>
@endif