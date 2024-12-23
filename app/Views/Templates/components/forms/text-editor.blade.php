@php
    use Leantime\Core\Support\EditorTypeEnum;
@endphp

@props([
    'type',
    'editorId' => md5(CURRENT_URL . $type),
    'value' => '',
    'name' => 'text',
    'customId' => null,
    'modal' => false,
    'diameter' => '',
])


@php
    $id = $customId ?? "editor-$editorId";
    $modalClass = $modal ? 'modalTextArea' : '';
@endphp

<div {{ $attributes->merge(['class' => '']) }}>
    <textarea rows="5" cols="50" class="{{ $type }} {{ $modalClass }} {{ $diameter }}"
        id="{{ $id }}" name="{{ $name }}">
        {{ $value }}
    </textarea>
</div>

<script type="module">
    import "@mix('/js/Components/editors.module.js')"
    jQuery(document).ready(function() {
        @if ($type == EditorTypeEnum::Simple->value)
            editors.initSimpleEditor(`{{ $id }}`);
        @elseif ($type == EditorTypeEnum::Complex->value)
            editors.initComplexEditor(`{{ $id }}`);
        @elseif ($type == EditorTypeEnum::Notes->value)
            editors.initNotesEditor(`{{ $id }}`);
        @endif
    });
</script>
