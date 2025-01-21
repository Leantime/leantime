@props([
    'contentRole' => 'primary',
    'variant' => 'chip', //chip, select
    'statuses' => [],
    'showLabel' => false,
    'labelPosition' => 'top',
    'dropdownPosition' => 'start',
    'goal' => null,
])

<x-global::forms.select name='status' search="false" :dropdown-position="$dropdownPosition" :label-position="$labelPosition" :variant="$variant"
    :content-role="$contentRole" hx-post="{{ BASE_URL }}/hx/goalCanvas/editCanvasItem/patch/{{ $goal->id }}"
    hx-trigger="change" hx-swap="none">

    @if ($showLabel)
        <x-slot:label-text>
            <x-global::content.icon icon="clock_loader_90" /> {!! __('label.status') !!}
        </x-slot:label-text>
    @endif


    @foreach ($statuses as $key => $label)
        <x-global::forms.select.option :value="strtolower($key)" :selected="strtolower($key) == strtolower($goal->status ?? '')">
            <x-global::elements.badge :state="$label['dropdown']" scale="sm" :outline="true">

                <x-global::content.icon :icon="$label['icon']" /> {{ $label['title'] }}
            </x-global::elements.badge>

        </x-global::forms.select.option>
    @endforeach
</x-global::forms.select>
