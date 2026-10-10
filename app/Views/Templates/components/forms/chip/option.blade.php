@props([
    'value',             // the raw value saved when picked (status key, user id, milestone id…)
    'label' => null,     // text shown on the chip after the pick (defaults to the slot's text)
    'colorClass' => '',  // color class of this value: painted on the item and put on the chip when picked
    'color' => '',       // background color of this value (milestones)
    'image' => '',       // avatar URL shown on the chip when picked (user chips)
])

{{--
    forms.chip.option — one value of a <x-global::forms.chip>. The slot is the item's content
    (text, or an avatar + name). Extra attributes (id, …) pass through to the <a>.
--}}
@php
    $chipLabel = $label ?? html_entity_decode(trim(strip_tags((string) $slot)), ENT_QUOTES | ENT_HTML5);
@endphp
<li class="dropdown-item">
    <a href="javascript:void(0);" {{ $attributes->class($colorClass !== '' ? [$colorClass] : []) }} data-value="{{ $value }}" data-label="{{ $chipLabel }}"@if ($colorClass !== '') data-class="{{ $colorClass }}"@endif @if ($color !== '') data-color="{{ $color }}" style="background-color:{{ $color }}"@endif @if ($image !== '') data-image="{{ $image }}"@endif>{{ $slot }}</a>
</li>
