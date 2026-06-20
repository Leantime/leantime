@props([
    // NO-OP variant -> the class the app renders TODAY. Default '' = a bare, unclassed input
    // (the common case: ~206 inputs have no class and are styled by their form/context).
    'variant' => '',          // '' (bare) | form | headline | large | small | legacy | search
    'inputType' => 'text',    // text | email | password | number | url | tel | search

    // --- design-system IDL: declared for the durable contract, but intentionally NOT rendered
    //     in no-op mode (a label/validation wrapper would change today's markup). They become
    //     active when the design phase introduces the field-row/label layout. ---
    'contentRole' => '',      // reserved
    'state' => '',            // info | warning | danger | success (validation) — reserved
    'scale' => '',            // xs | s | m | l | xl — reserved
    'labelPosition' => 'top', // reserved
    'labelText' => '',        // reserved
    'caption' => '',          // reserved
    'validationText' => '',   // reserved
    'validationState' => '',  // reserved
    'leadingVisual' => '',    // reserved
    'trailingVisual' => '',   // reserved
])

{{--
    forms.text-input — NO-OP text input.

    Renders a plain <input> with the SAME class the app uses TODAY (default: NO class). Every
    other attribute (name, id, value, placeholder, style, data-*, hx-*, autocomplete, required,
    maxlength, autofocus, …) passes straight through via $attributes. Zero visual/behaviour change.

    ⚠️ DO NOT route JS-coupled inputs through this component — they will break. Keep these RAW:
      • date pickers: .dates .duedates .quickDueDates .dateFrom .dateTo .editFrom .editTo
        .startDate .endDate .projectDateFrom .projectDateTo .week-picker .hasDatepicker
        #deadline #sprintStart #sprintEnd #event_date_* #date #startDate #endDate #timesheetdate …
      • time: .timepicker, type="time"        • tags: #tags .tagsinputField data-role="tagsinput"
      • inline-edit: .secretInput .asyncInputUpdate (+ data-label / data-id)
      • color: .simpleColorPicker              • any inline onchange/onblur/onkeyup/oninput handler
    See COMPONENTS.md for the full do-not-touch list.

    Pass the input type via the `inputType` prop, NOT a `type="…"` attribute (the component emits
    `type` itself; a `type` attribute would duplicate it).

    Migration cheatsheet (source class -> variant):
      <input type="text" name=…>                 -> <x-global::forms.text-input name=…>      (bare, no class)
      <input type="email" class="form-control">  -> inputType="email" variant="form"
      <input class="main-title-input">           -> variant="headline"
      <input class="input-large">                -> variant="large"
      <input class="input"> (legacy)             -> variant="legacy"
--}}
@php
    // No-op map: variant -> the exact class the markup uses today.
    $variantClass = match ($variant) {
        'form', 'bordered' => 'form-control',
        'headline', 'title' => 'main-title-input',
        'large' => 'input-large',
        'small' => 'input-small',
        'legacy' => 'input',
        default => '',   // bare / search: no class (styled by context / id / name)
    };

    // Only add a class attribute when there's actually a class — so a bare input stays
    // class-less (no empty class="") exactly like today.
    $attrs = $variantClass !== '' ? $attributes->merge(['class' => $variantClass]) : $attributes;
@endphp

<input type="{{ $inputType }}" {{ $attrs }} />
