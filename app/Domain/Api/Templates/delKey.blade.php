<div class="pageheader">
    <div class="pageicon"><i class="fa-solid fa-key" aria-hidden="true"></i></div>
    <div class="pagetitle">
        <h5>{{ $tpl->__('label.administration') }}</h5>
        <h1>{{ $tpl->__('headlines.delete_key') }}</h1>
    </div>
</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">

        {!! $tpl->displayNotification() !!}

        <h5 class="subtitle">{{ $tpl->__('subtitles.delete_key') }}</h5>

            <form method="post">
                <input type="hidden" name="{{ session('formTokenName') }}" value="{{ session('formTokenValue') }}" />
                <p>{{ $tpl->__('text.confirm_key_deletion') }}</p><br />
                <x-globals::forms.button submit type="danger" name="del">{{ $tpl->__('buttons.yes_delete') }}</x-globals::forms.button>
                <x-globals::forms.button link="{{ BASE_URL }}/setting/editCompanySettings/#apiKeys" type="primary">{{ $tpl->__('buttons.back') }}</x-globals::forms.button>
            </form>

    </div>
</div>
