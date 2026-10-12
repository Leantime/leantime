{{--
    forms.radio — one radio button with its label (and an optional description line).

    Give every radio in a group the same `name`. The label wraps the control, so clicking the text
    picks it. Same look as forms.checkbox.

    Props:
      name         string  Group name. REQUIRED.
      value        string  Value posted when picked. REQUIRED.
      checked      bool    Starts picked.
      id           string  Input id; generated when omitted.
      description  string  One muted line under the label.

    Slot: the label text (markup allowed; escape user text at the call site).

    Anything else (onchange, hx-*, disabled, data-*) passes through to the <input>.
    Use `wrapperClass` for layout classes on the label wrapper.
--}}
@props([
    'name',
    'value',
    'checked' => false,
    'id' => null,
    'description' => null,
    'wrapperClass' => '',
])

@php
    $id = $id ?: 'lt-radio-' . bin2hex(random_bytes(4));
@endphp

<label for="{{ $id }}" @class(['lt-check', $wrapperClass])>
    <input type="radio" id="{{ $id }}" name="{{ $name }}" value="{{ $value }}"
        @checked($checked)
        {{ $attributes->class(['lt-check-input']) }} />
    <span class="lt-check-text">
        <span class="lt-check-label">{{ $slot }}</span>
        @if ($description)
            <span class="lt-check-desc">{{ $description }}</span>
        @endif
    </span>
</label>
