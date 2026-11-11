{{--
    forms.field — one form field: its label, the control and an optional help line.

    The field owns the spacing (10px to the next field), so forms stop relying on each control's own
    margin and on `<br />` between fields.

    Layouts:
      stacked (default)  label above the control: dialogs, side panels, narrow forms.
      inline             label in a column on the left (`labelWidth`, default 150px), control beside
                         it: wide settings forms. Stacks on phones.

    Props:
      label       string  The label text (escaped). For markup (an icon, a link) use the `label` slot.
      for         string  Id of the control, so clicking the label focuses it.
      required    bool    Required marker after the label.
      help        string  One muted 12px line under the control (escaped).
      layout      string  'stacked' (default) or 'inline'.
      labelWidth  int     Label column width in px for `inline` (default 150).

    Slots:
      default  The control: forms.text-input, forms.select, forms.date-picker, an editor …
      label    The label with markup, instead of the `label` prop.

    Anything else (id, data-*, extra classes) passes through to the wrapper.
--}}
@props([
    'label' => null,
    'for' => null,
    'required' => false,
    'help' => null,
    'layout' => 'stacked',
    'labelWidth' => null,
])

@php
    $isInline = $layout === 'inline';
    $widthStyle = $isInline && $labelWidth ? ['style' => '--lt-field-label-w: '.(int) $labelWidth.'px'] : [];
@endphp

<div {{ $attributes->class(['lt-field', 'lt-field--inline' => $isInline])->merge($widthStyle) }}>
    @if (filled((string) $label))
        <x-global::forms.label :for="$for" :required="$required" class="lt-field-label">{{ $label }}</x-global::forms.label>
    @endif
    <div class="lt-field-control">
        {{ $slot }}
        @if (filled($help))
            <p class="lt-field-help">{{ $help }}</p>
        @endif
    </div>
</div>
