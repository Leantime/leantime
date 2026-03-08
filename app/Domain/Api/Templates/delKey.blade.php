<x-globals::layout.page-header icon="key" headline="{{ $tpl->__('headlines.delete_key') }}" subtitle="{{ $tpl->__('label.administration') }}" />

<div class="maincontent">
    <div class="maincontentinner">

        {!! $tpl->displayNotification() !!}

        <h5 class="subtitle">{{ $tpl->__('subtitles.delete_key') }}</h5>

            <form method="post">
                <input type="hidden" name="{{ session('formTokenName') }}" value="{{ session('formTokenValue') }}" />
                <p>{{ $tpl->__('text.confirm_key_deletion') }}</p><br />
                <x-globals::forms.button :submit="true" state="danger" name="del">{{ $tpl->__('buttons.yes_delete') }}</x-globals::forms.button>
                <x-globals::forms.button element="a" href="{{ BASE_URL }}/setting/editCompanySettings/#apiKeys" contentRole="primary">{{ $tpl->__('buttons.back') }}</x-globals::forms.button>
            </form>

    </div>
</div>
