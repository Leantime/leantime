{{--
    forms.tags — a tag field: chips you can type, pick from suggestions or remove.

    Built on the enhanced select (SlimSelect, core/selects.js) so it looks and behaves like every
    other select: chips inside the field, suggestions from Tags.Tags.getTags over JSON-RPC, and
    Enter adds the typed text as a new tag (commas inside it become spaces).

    The backend contract does not change: a hidden input with `name` / `id` carries the tags as one
    comma-separated string, exactly what the old jQuery tagsInput field posted. It fires `change`
    whenever the tags change, so autosave listeners can watch it.

    Props:
      name         string  Field name of the hidden input (default "tags").
      id           string  Id of the hidden input (default = name).
      value        string  Current tags, comma-separated.
      placeholder  string  Placeholder text.
      width        string  sm | md | lg | full | auto — the shared field-width scale (default md).

    Anything else passes through to the wrapper.
--}}
@props([
    'name' => 'tags',
    'id' => null,
    'value' => '',
    'placeholder' => null,
    'width' => 'md',
])

@php
    $id = $id ?? $name;
    $tags = array_values(array_unique(array_filter(array_map('trim', explode(',', (string) $value)), 'strlen')));
    $placeholder = $placeholder ?? __('input.placeholders.add_tags');
    $width = in_array($width, ['sm', 'md', 'lg', 'full', 'auto'], true) ? $width : 'md';
@endphp

<div {{ $attributes->class(['lt-tags']) }}>
    <input type="hidden" name="{{ $name }}" id="{{ $id }}" value="{{ implode(',', $tags) }}" />
    <select multiple
            class="field-width-{{ $width }}"
            data-lt-select
            data-lt-tags="{{ $id }}"
            data-search="true"
            data-close-on-select="false"
            data-placeholder="{{ $placeholder }}"
            aria-label="{{ __('label.tags') }}">
        @foreach ($tags as $tag)
            <option value="{{ $tag }}" selected>{{ $tag }}</option>
        @endforeach
    </select>
</div>
