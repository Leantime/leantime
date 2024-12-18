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

@once
    @push('scripts')
        <script type="module">
            import "@mix('/js/Components/editors.module.mjs')";
            jQuery(document).ready(function() {
                @if ($type == EditorTypeEnum::Simple->value)
                    leantime.editorController.initSimpleEditor(`{{ $id }}`);
                @elseif ($type == EditorTypeEnum::Complex->value)
                    leantime.editorController.initComplexEditor(`{{ $id }}`);
                @elseif ($type == EditorTypeEnum::Notes->value)
                    leantime.editorController.initNotesEditor(`{{ $id }}`);
                @endif
            });
        </script>
    @endpush
@endonce
