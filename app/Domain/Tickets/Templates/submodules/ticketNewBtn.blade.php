@if (can('tickets.create') && !empty($newField))
    <x-global::actions.dropdown variant="button" class="pull-left" style="margin-right:5px;">
        <x-slot:trigger>{!! __('links.new_with_icon') !!} <span class="caret"></span></x-slot:trigger>
        @foreach ($newField as $option)
            <li>
                <a
                    href="{{ !empty($option['url']) ? $option['url'] : '' }}"
                    class="{{ !empty($option['class']) ? $option['class'] : '' }}"
                > {!! !empty($option['text']) ? __($option['text']) : '' !!}</a>
            </li>
        @endforeach

    </x-global::actions.dropdown>
@endif
