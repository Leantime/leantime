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
    <h4 class="widgettitle title-light"><x-global::elements.icon name="edit" /> {{ __('label.edit_label') }}</h4>

    {!! $tpl->displayNotification() !!}

    <form class="formModal" method="post" action="{{ BASE_URL }}/setting/editBoxLabel?module={{ e($_GET['module']) }}&label={{ e($_GET['label']) }}">

        <label>{{ __('label.label') }}</label>
        <x-globals::forms.input name="newLabel" value="{{ $currentLabel }}" /><br />

        <x-globals::forms.button submit type="primary">{{ __('buttons.save') }}</x-globals::forms.button>

    </form>
@endif
