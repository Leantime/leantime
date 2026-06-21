@props([
    // NO-OP textarea: renders a plain <textarea> with today's attributes + inner content.
    // There is NO `variant` arm: the only textarea style-classes in the app (.tiptapSimple /
    // .tiptapComplex / .wiki-editor-textarea) are JS rich-text EDITOR mounts — never route those
    // through this component (see do-not-touch below). Plain textareas carry no distinct style
    // class, so attribute + content passthrough is the whole no-op surface.

    // --- design-system IDL: declared for the durable contract (shared with forms.text-input),
    //     intentionally NOT rendered in no-op mode (a label/validation wrapper would change
    //     today's markup). Activated in the design phase's field-row layout. ---
    'contentRole' => '',      // reserved
    'state' => '',            // info | warning | danger | success (validation) — reserved
    'scale' => '',            // xs | s | m | l | xl — reserved
    'labelPosition' => 'top', // reserved
    'labelText' => '',        // reserved
    'caption' => '',          // reserved
    'validationText' => '',   // reserved
    'validationState' => '',  // reserved
])

{{--
    forms.textarea — NO-OP textarea.

    Renders a plain <textarea> with the SAME attributes the app uses today; every attribute
    (name, id, rows, cols, placeholder, style, class, data-*, hx-*, required, …) passes through
    via $attributes, and the field's value is the slot (inner content), preserved EXACTLY.

    ⚠️ DO NOT route rich-text EDITOR textareas through this component — JS upgrades them to Tiptap
    and they will break. Keep these RAW:
      • Tiptap: class="tiptapSimple" / class="tiptapComplex"  (JS scans `textarea.tiptapSimple` /
        `textarea.tiptapComplex` and mounts an editor — public/assets/js/app/core/tiptap/index.js)
      • Wiki editor: class="wiki-editor-textarea" (id="wikiArticleContent")
      • any textarea with an inline on* handler or a data-*editor* attribute.

    ⚠️ Whitespace matters: a textarea's value IS its inner content. Keep the slot tight —
    <x-global::forms.textarea …>{{ $value }}</x-global::forms.textarea> — never add newlines/indent
    around the value, or you change the field's content.

    Migration:
      <textarea name="x"></textarea>          -> <x-global::forms.textarea name="x"></x-global::forms.textarea>
      <textarea name="x">{{ $v }}</textarea>  -> <x-global::forms.textarea name="x">{{ $v }}</x-global::forms.textarea>
--}}
<textarea {{ $attributes }}>{{ $slot }}</textarea>
