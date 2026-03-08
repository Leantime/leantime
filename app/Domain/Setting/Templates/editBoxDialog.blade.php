@php
    $currentLabel = $tpl->get('currentLabel');
@endphp

@if (request()->isMethod('post'))
    <script>
        if (typeof leantime !== 'undefined' && leantime.modals) {
            leantime.modals.closeModal();
        }
    </script>
@else
    <x-globals::elements.section-title icon="edit">{{ __('label.edit_label') }}</x-globals::elements.section-title>

    {!! $tpl->displayNotification() !!}

    <form class="formModal" method="post" action="{{ BASE_URL }}/setting/editBoxLabel?module={{ e($_GET['module']) }}&label={{ e($_GET['label']) }}">

        <label>{{ __('label.label') }}</label>
        <x-globals::forms.text-input name="newLabel" value="{{ $currentLabel }}" /><br />

        <x-globals::forms.button :submit="true" contentRole="primary">{{ __('buttons.save') }}</x-globals::forms.button>

    </form>
@endif
