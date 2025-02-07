@props([
    'contentRole' => 'primary',
    'variant' => 'chip', //chip, select
    'statuses' => [],
    'showLabel' => false,
    'labelPosition' => 'top',
    'dropdownPosition' => 'left',
    'idea' => null,
])

<x-global::forms.select name='status' search="false" :dropdown-position="$dropdownPosition" :label-position="$labelPosition" :variant="$variant"
    :content-role="$contentRole" hx-post="{{ BASE_URL }}/hx/ideas/ideaDialog/patch/{{ $idea->id }}" hx-trigger="change"
    hx-swap="none">

    @if ($showLabel)
        <x-slot:label-text>
            <x-global::content.icon icon="clock_loader_90" /> {!! __('label.status') !!}
        </x-slot:label-text>
    @endif



    @foreach ($statuses as $key => $label)
        <x-global::forms.select.option :value="strtolower($key)" :selected="strtolower($key) == strtolower($idea->status ?? '')">

            <x-global::elements.badge :state="trim($label['class'], 'lable-')" :outline="true">
                {{ $label['name'] }}
            </x-global::elements.badge>

        </x-global::forms.select.option>
    @endforeach
</x-global::forms.select>
