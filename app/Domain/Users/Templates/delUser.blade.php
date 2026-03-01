@php
    $user = $tpl->get('user');
@endphp

<div class="pageheader">
    <div class="pageicon"><x-global::elements.icon :name="$tpl->getModulePicture()" /></div>
    <div class="pagetitle">
        <h5>{{ __('label.administration') }}</h5>
        <h1>{{ __('headlines.delete_user') }}</h1>
    </div>
</div>

<div class="maincontent">
    <div class="maincontentinner">

        {!! $tpl->displayNotification() !!}

        <h4 class="widget widgettitle">{{ __('subtitles.delete') }}</h4>
        <div class="widgetcontent">

            <form method="post">
                <input type="hidden" name="{{ session('formTokenName') }}" value="{{ session('formTokenValue') }}" />
                <p>{{ __('text.confirm_user_deletion') }}</p><br />
                <x-globals::forms.button submit type="danger" name="del">{{ __('buttons.yes_delete') }}</x-globals::forms.button>
                <x-globals::forms.button link="{{ BASE_URL }}/users/showAll" type="primary">{{ __('buttons.back') }}</x-globals::forms.button>
            </form>

        </div>
    </div>
</div>
