@php
    $user = $tpl->get('user');
@endphp

<div class="pageheader">
    <div class="pageicon"><span class="fa {{ $tpl->getModulePicture() }}"></span></div>
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
                <input type="submit" value="{{ __('buttons.yes_delete') }}" name="del" class="button" />
                <a class="btn btn-primary" href="{{ BASE_URL }}/users/showAll">{{ __('buttons.back') }}</a>
            </form>

        </div>
    </div>
</div>
