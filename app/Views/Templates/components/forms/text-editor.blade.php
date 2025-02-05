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

    $textareaAttributes = [
        'class' => "$type $modalClass $diameter",
        'rows' => 5,
        'cols' => 50,
        'id' => $id,
        'name' => $name,
        'data-component' => 'editor',
        'data-component-config' => json_encode(['type' => $type]),
    ];
@endphp

<div {{ $attributes->whereDoesntStartWith('hx-')->merge(['class' => '']) }}>
    <div class="editor-loading-state">
        <x-global::elements.loadingText type="text" count="1" class="w-full min-w-[150px]" />
    </div>

    <textarea {{ $attributes->merge($textareaAttributes)->merge($attributes->whereStartsWith('hx-')->getAttributes()) }}>
        {{ $value }}
    </textarea>
</div>

{{--    import "@mix('/js/Components/editors.module.js')" --}}
{{--    jQuery(document).ready(function() { --}}
{{--        @if ($type == EditorTypeEnum::Simple->value) --}}
{{--            editors.initSimpleEditor(`{{ $id }}`); --}}
{{--        @elseif ($type == EditorTypeEnum::Complex->value) --}}
{{--            editors.initComplexEditor(`{{ $id }}`); --}}
{{--        @elseif ($type == EditorTypeEnum::Notes->value) --}}
{{--            editors.initNotesEditor(`{{ $id }}`); --}}
{{--        @endif --}}
{{--    }); --}}

@if ($attributes->has('hx-post'))
    <script type="module">
        jQuery(document).ready(function() {
            const editorEl = document.getElementById('{{ $id }}');
            let debounceTimer;
            editorEl.addEventListener('editor-change', function(e) {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    htmx.trigger(editorEl, 'change');
                }, 500);
            })
        });
    </script>
@endif
