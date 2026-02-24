<div class="pageheader">
    <div class="pageicon"><i class="fa-solid fa-key"></i></div>
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
                <x-global::button submit type="danger" name="del">{{ $tpl->__('buttons.yes_delete') }}</x-global::button>
                <x-global::button link="{{ BASE_URL }}/setting/editCompanySettings/#apiKeys" type="primary">{{ $tpl->__('buttons.back') }}</x-global::button>
            </form>

    </div>
</div>
