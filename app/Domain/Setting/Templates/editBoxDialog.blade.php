@php
    $currentLabel = $tpl->get('currentLabel');
@endphp

<h4 class="widgettitle title-light">{{ __('headlines.edit_label') }}</h4>

{!! $tpl->displayNotification() !!}

<form class="formModal" method="post" action="{{ BASE_URL }}/setting/editBoxLabel?module={{ e($_GET['module']) }}&label={{ e($_GET['label']) }}">

    <label>{{ __('label.label') }}</label>
    <x-global::forms.input name="newLabel" value="{{ $currentLabel }}" /><br />

    <x-global::button submit type="primary">{{ __('buttons.save') }}</x-global::button>

</form>
