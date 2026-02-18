@php $tpl = $__data['tpl']; @endphp
<div class="pageheader">
    <div class="pagetitle">
        <h1>{{ $tpl->language->__('headlines.installation') }}</h1>
    </div>
</div>
<div class="regcontent" id="login">
    <p>{{ $tpl->language->__('text.this_script_will_set_up_leantime') }}</p><br />

    {!! $tpl->displayInlineNotification() !!}

    <form action="{{ BASE_URL }}/install" method="post" class="registrationForm">
        <h3 class="subtitle">{{ $tpl->language->__('subtitles.login_info') }}</h3>
        <x-global::forms.input type="email" name="email" placeholder="{{ $tpl->language->__('label.email') }}" value="" /><br />
        <br /><br />
        <h3 class="subtitle">{{ $tpl->language->__('subtitles.user_info') }}</h3>
        <x-global::forms.input name="firstname" placeholder="{{ $tpl->language->__('label.firstname') }}" value="" /><br />
        <x-global::forms.input name="lastname" placeholder="{{ $tpl->language->__('label.lastname') }}" value="" />
        <x-global::forms.input name="company" placeholder="{{ $tpl->language->__('label.company_name') }}" value="" />
        <br /><br />
        <input type="hidden" name="install" value="Install" />
        <p><x-global::button submit type="primary" name="installAction" onClick="this.form.submit(); this.disabled=true; this.value='{{ $tpl->language->__('buttons.install') }}';">{{ $tpl->language->__('buttons.install') }}</x-global::button></p>
    </form>

</div>
