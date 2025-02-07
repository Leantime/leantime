@props([
    'contentRole' => 'ghost',
    'variant' => '',
    'labelPosition' => 'top',
    'goal' => null,
    'showLabel' => false,
])


<x-global::forms.datepicker no-date-label="{{ __('text.anytime') }}" :value="$goal->startDate" name="startDate"
    dateName="dueDate-{{ $goal->id }}" :label-position="$labelPosition" :variant="$variant"
    hx-post="{{ BASE_URL }}/hx/goalCanvas/editCanvasItem/patch/{{ $goal->id }}" hx-trigger="change" hx-swap="none"
    :content-role="$contentRole" :state="$state">
    <x-slot:leading-visual>
        <x-global::content.icon icon="alarm" class="text-lg text-trivial" />
    </x-slot:leading-visual>

    @if ($showLabel)
        <x-slot:label-text>
            <x-global::content.icon icon="alarm" /> <span>{!! __('label.due') !!}</span>
        </x-slot:label-text>
    @endif

</x-global::forms.datepicker>
