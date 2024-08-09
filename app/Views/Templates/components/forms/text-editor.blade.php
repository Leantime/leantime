@php
    use Leantime\Core\Support\EditorTypeEnum;
@endphp

@props([
    'type',
    'editorId' => md5(CURRENT_URL.$type->value),
    "value"
])

<div {{ $attributes->merge([ 'class' => '' ]) }}>
    <textarea rows="5" cols="50" class="{{ $type->value }}" id="editor-{{ $editorId }}" name="text">
        {{ $value }}
    </textarea>
</div>

@once
    <script type="text/javascript">
        jQuery(document).ready(function() {
            @if($type == EditorTypeEnum::Simple)
                leantime.editorController.initSimpleEditor();
            @elseif($type == EditorTypeEnum::Complex)
                leantime.editorController.initComplexEditor();
            @elseif($type == EditorTypeEnum::Notes)
                leantime.editorController.initNotesEditor();
            @endif
        });
    </script>
@endonce

