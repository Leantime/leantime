{{--
    forms.checkbox — a checkbox or on/off toggle with its label (and an optional description line).

    The label wraps the control, so clicking the text toggles it. Label text is body size (14 / regular);
    the description is 12px muted (type rule in COMPONENTS.md).

    Props:
      name         string  Field name.
      value        string  Value posted when checked, default '1'.
      checked      bool    Starts checked.
      id           string  Input id; generated when omitted.
      variant      string  'checkbox' (default) | 'toggle' (on/off switch for a setting that applies at once).
      description  string  One muted line under the label.

    Slot: the label text. It may contain markup (translations with icons); escape user text at the
    call site. Leave it empty for a bare control labelled some other way (pass aria-label).

    Anything else (onchange, hx-*, disabled, data-*, aria-*) passes through to the <input>.
    Use `wrapperClass` for layout classes on the label wrapper.
--}}
@props([
    'name' => null,
    'value' => '1',
    'checked' => false,
    'id' => null,
    'variant' => 'checkbox',
    'description' => null,
    'wrapperClass' => '',
])

@php
    $id = $id ?: 'lt-check-' . bin2hex(random_bytes(4));
    $isToggle = $variant === 'toggle';
    $hasLabel = trim((string) $slot) !== '';
@endphp

<label for="{{ $id }}" @class(['lt-check', 'lt-check--toggle' => $isToggle, $wrapperClass])>
    <input type="checkbox" id="{{ $id }}" value="{{ $value }}"
        @if ($name !== null) name="{{ $name }}" @endif
        @checked($checked)
        {{ $attributes->class(['lt-check-input', 'toggle' => $isToggle]) }} />
    @if ($hasLabel || $description)
        <span class="lt-check-text">
            @if ($hasLabel)
                <span class="lt-check-label">{{ $slot }}</span>
            @endif
            @if ($description)
                <span class="lt-check-desc">{{ $description }}</span>
            @endif
        </span>
    @endif
</label>
