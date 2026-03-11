@php
    $user = $tpl->get('user');
@endphp

<x-globals::layout.page-header :icon="$tpl->getModulePicture()" headline="{{ __('headlines.delete_user') }}" subtitle="{{ __('label.administration') }}" />

<div class="maincontent">
    <div class="maincontentinner">

        {!! $tpl->displayNotification() !!}

        <x-globals::elements.section-title variant="plain" icon="delete">{{ __('label.delete') }}</x-globals::elements.section-title>
        <div class="widgetcontent">

            <form method="post">
                <input type="hidden" name="{{ session('formTokenName') }}" value="{{ session('formTokenValue') }}" />
                <p>{{ __('text.confirm_user_deletion') }}</p><br />
                <x-globals::forms.button :submit="true" state="danger" name="del">{{ __('buttons.yes_delete') }}</x-globals::forms.button>
                <x-globals::forms.button element="a" href="{{ BASE_URL }}/users/showAll" contentRole="primary">{{ __('buttons.back') }}</x-globals::forms.button>
            </form>

        </div>
    </div>
</div>
