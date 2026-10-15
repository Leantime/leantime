{{--
    forms.label — the one field label.

    One look everywhere (dialogs, settings, pages): 14px / semibold / text colour. The base
    `form label` rule in forms.css matches it, so labels not yet migrated look the same.
    Layout (beside the field vs above it) is the caller's: pass width / margin classes
    (e.g. `tw-w-[100px] tw-shrink-0` for a label column), the label itself stays the same.

    Props:
      for       string  Optional id of the field it labels.
      required  bool    Adds a required marker after the text.

    Slot: the label text. It may contain markup (translations with icons), so it is not escaped
    here — escape user-provided text at the call site.

    Anything else (id, class, data-*) passes through to the <label>.
--}}
@props([
    'for' => null,
    'required' => false,
])

<label {{ $attributes->class(['lt-label'])->merge(array_filter(['for' => $for])) }}>{{ $slot }}@if ($required)<span class="lt-label-required" aria-hidden="true">*</span>@endif</label>
