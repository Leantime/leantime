@props([
    // NO-OP select: renders a plain <select> with today's attributes; the <option>/<optgroup>
    // markup is the slot, passed through untouched. No `variant` arm yet — the classes found on
    // selects today (span11, user-select, form-control, tw-*) are context/JS hooks, not distinct
    // visual treatments, so they pass through via class="…".

    // --- design-system IDL: declared for the durable contract (shared with forms.text-input /
    //     forms.textarea), intentionally NOT rendered in no-op mode (a label/validation wrapper
    //     would change today's markup). Activated in the design phase's field-row layout. ---
    'contentRole' => '',      // reserved
    'state' => '',            // info | warning | danger | success (validation) — reserved
    'scale' => '',            // xs | s | m | l | xl — reserved
    'labelPosition' => 'top', // reserved
    'labelText' => '',        // reserved
    'caption' => '',          // reserved
    'validationText' => '',   // reserved
    'validationState' => '',  // reserved
    'leadingVisual' => '',    // reserved
])

{{--
    forms.select — NO-OP native select.

    Renders a plain <select> with the SAME attributes the app uses today. Every attribute except the
    declared @props above (name, id, class, style, multiple, required, disabled, data-*, hx-*, onchange, …)
    passes straight through via $attributes — directly on the <select>, with no wrapper element, so
    hx-trigger="change", hx-include, hx-vals and inline onchange behave exactly like raw markup.
    The options are the slot.

    JS-enhanced selects (Chosen / SlimSelect today) still work: the markup is identical, so their
    existing initializers bind as before. The enhanced mode (`enhanced` prop + SlimSelect v2 registry)
    and the option/optgroup sub-components arrive in the next phase — see COMPONENTS.md.

    Boolean attributes: write them bare (`multiple`, `required`) or bound (`:disabled="$isLocked"`).
    Blade component tags do NOT support directives (@if, @disabled, @selected) or bare {{ }} echoes
    inside the opening tag — use a bound attribute instead.

    Migration:
      <select name="role" id="role">…</select>   -> <x-global::forms.select name="role" id="role">…</x-global::forms.select>
--}}
<select {{ $attributes }}>{{ $slot }}</select>
