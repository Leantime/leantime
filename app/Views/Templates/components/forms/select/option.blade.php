@props([
    'value' => '',
    'selected' => false,
    'disabled' => false,
    'icon' => '',        // Font Awesome classes, e.g. "fa-solid fa-circle-check"
    'color' => '',       // icon color (hex or CSS var), e.g. "#1B75BB" / "var(--accent1)"
    'colorClass' => '',  // label class painted behind the text, e.g. "label-purple"
])

{{--
    forms.select.option — an <option> that can carry an icon or a color swatch.

    A native select only shows the text (options can't render markup). An `enhanced` select shows the
    icon / colored label too: this renders it as `data-html`, which SlimSelect v2 reads off the option.
    The slot is the option label.

      <x-global::forms.select.option :value="$key" :selected="$current == $key" icon="fa-solid {{ $label['icon'] }}" color="#1B75BB">
          {{ $label['title'] }}
      </x-global::forms.select.option>

    Plain options (no icon/color) don't need this component — a raw <option> is fine.
--}}
@php
    // The slot is already-escaped HTML (it came through {{ }}), so it can go into data-html as is;
    // Blade escapes the attribute value once more and the browser decodes it back before SlimSelect
    // sets it as innerHTML.
    $labelHtml = trim((string) $slot);
    $richHtml = '';
    if ($icon !== '') {
        $iconStyle = $color !== '' ? ' style="color:'.e($color).'"' : '';
        $richHtml = '<i class="fa-fw '.e($icon).'"'.$iconStyle.' aria-hidden="true"></i>&nbsp;'.$labelHtml;
    } elseif ($colorClass !== '') {
        $richHtml = '<span class="'.e($colorClass).'">'.$labelHtml.'</span>';
    }

    // The color lives only in data-html: the enhanced dropdown copies an option's class onto its
    // list row, which would paint the whole row instead of the label pill.
@endphp
<option value="{{ $value }}" @selected($selected) @disabled($disabled) {{ $attributes }}@if ($richHtml !== '') data-html="{{ $richHtml }}"@endif>{{ $slot }}</option>
