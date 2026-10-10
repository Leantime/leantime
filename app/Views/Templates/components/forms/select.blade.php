@props([
    // NO-OP select: renders a plain <select> with today's attributes; the <option>/<optgroup>
    // markup is the slot, passed through untouched. No `variant` arm yet — the classes found on
    // selects today (span11, user-select, form-control, tw-*) are context/JS hooks, not distinct
    // visual treatments, so they pass through via class="…".

    // --- enhanced mode (SlimSelect v2, wired by public/assets/js/app/core/selects.js) ---
    'enhanced' => false,      // true -> searchable/styled dropdown; false -> plain native <select>
    'search' => 'auto',       // auto (search box when > 10 options) | true | false
    'allowDeselect' => false, // single selects: show an "x" to clear the value
    'closeOnSelect' => true,  // multi selects: keep the list open while picking (false)

    'width' => null,          // sm | md | lg | full | auto — field-width scale shared with forms.text-input
                              // (forms.css). Omit for the default (md). Never `size`: that's the native
                              // listbox-rows attribute.
    // Placeholder text comes from the existing `data-placeholder="…"` attribute (passes through).

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

    Enhanced: add `enhanced` and the select becomes a SlimSelect v2 dropdown. Never call
    `new SlimSelect(...)` from a template — the registry in core/selects.js picks up every
    `select[data-lt-select]` on first paint, after every htmx swap and inside modals, and destroys
    the instance when htmx or the modal removes the select. The native <select> stays the source of
    truth: it keeps its name/value, still fires `change` (so hx-trigger="change" and jQuery
    .change() handlers work), and adding/removing/hiding <option>s re-syncs the dropdown.

      <x-global::forms.select name="editorId" enhanced data-placeholder="{{ __('label.filter_by_user') }}">…</x-global::forms.select>

    Icons / colors per option: use <x-global::forms.select.option> (renders `data-html`, which the
    enhanced dropdown shows; a native select just shows the text).

    Width: every select (and text input) is `md` unless it asks for another size of the shared scale —
    `width="sm"` for numbers/times/short choices, `lg` for long titles, `full` to fill the container,
    `auto` for content width. Don't set pixel widths inline. A leading blank `<option value=""></option>`
    is treated as the placeholder.

    Setting a value from code: leantime.selectController.setValue(select, value) — works enhanced or
    not, and fires `change` like a user pick. It rewrites the options from the dropdown's own copy, so
    hide/show options AFTER setting the value, not before.

    Boolean attributes: write them bare (`multiple`, `required`) or bound (`:disabled="$isLocked"`).
    Blade component tags do NOT support directives (@if, @disabled, @selected) or bare {{ }} echoes
    inside the opening tag — use a bound attribute instead.

    Migration:
      <select name="role" id="role">…</select>   -> <x-global::forms.select name="role" id="role">…</x-global::forms.select>
--}}
@php
    // Only enhanced selects carry data-lt-select*, so a plain select's markup stays exactly as written.
    // (Not $attributes->merge(): merge() rewrites the style attribute.)
    $searchSetting = is_bool($search) ? ($search ? 'true' : 'false') : (string) $search;
    // class() leaves the style attribute alone; SlimSelect copies the class onto its control.
    $attrs = in_array($width, ['sm', 'md', 'lg', 'full', 'auto'], true)
        ? $attributes->class(['field-width-'.$width])
        : $attributes;
@endphp
<select {{ $attrs }}@if ($enhanced) data-lt-select="true" data-search="{{ $searchSetting }}" data-allow-deselect="{{ $allowDeselect ? 'true' : 'false' }}" data-close-on-select="{{ $closeOnSelect ? 'true' : 'false' }}"@endif>{{ $slot }}</select>
