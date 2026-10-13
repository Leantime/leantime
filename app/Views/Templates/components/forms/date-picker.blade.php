{{--
    forms.date-picker — a date field with the app's date picker (user's date format and language).

    Initialised by core/datePickers.js on render and after every htmx swap, and torn down when htmx
    removes it, so pages never call initDatePicker themselves.

    Props:
      name         string  Field name.
      id           string  Input id; generated when omitted.
      value        mixed   A date already in the user's format, or a DateTimeInterface / Carbon value
                           (formatted with the user's date format).
      range        string  Pair two fields: 'from' or 'to'. The 'to' field can't be before 'from',
                           and an empty 'to' takes the 'from' date.
      rangeGroup   string  Name that pairs a 'from' with its 'to' (default 'range'); give each pair
                           on a page its own name.
      width        string  Field width: 'sm' (default) | 'md' | 'lg' | 'full' | 'auto'.

    Anything else (form, required, readonly, onchange, hx-*, data-*) passes through to the <input>.
    readonly fields don't open the picker.
--}}
@props([
    'name' => null,
    'id' => null,
    'value' => '',
    'range' => null,
    'rangeGroup' => 'range',
    'width' => 'sm',
])

@php
    $id = $id ?: 'lt-date-' . bin2hex(random_bytes(4));
    // format() takes DateTime or Carbon; normalise any DateTimeInterface (e.g. DateTimeImmutable) first.
    $displayValue = $value instanceof \DateTimeInterface
        ? format(\Carbon\CarbonImmutable::instance($value))->date()
        : (string) $value;
    $range = in_array($range, ['from', 'to'], true) ? $range : null;
    $width = in_array($width, ['sm', 'md', 'lg', 'full', 'auto'], true) ? $width : 'sm';
@endphp

<input type="text" id="{{ $id }}" value="{{ $displayValue }}" autocomplete="off"
    @if ($name !== null) name="{{ $name }}" @endif
    placeholder="{{ __('language.dateformat') }}"
    data-lt-datepicker
    @if ($range) data-lt-date-range="{{ $rangeGroup }}" data-lt-date-role="{{ $range }}" @endif
    {{ $attributes->class(['lt-date-input', 'field-width-' . $width]) }} />
