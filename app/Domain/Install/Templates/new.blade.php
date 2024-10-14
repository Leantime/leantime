@extends($layout)

@section('content')

<div class="pageheader">
    <div class="pagetitle">
        <h1><?php echo $tpl->language->__("headlines.installation"); ?></h1>
    </div>

</div>
<div class="regcontent"  id="login">
    <p><?php echo $tpl->language->__("text.this_script_will_set_up_leantime"); ?></p><br />

    <?php echo $tpl->displayInlineNotification(); ?>

    <form action="{{ BASE_URL }}/install" method="post" class="registrationForm">
        @csrf <!-- Include CSRF token for security -->
    
        <h3 class="subtitle">{{ __('subtitles.login_info') }}</h3>
    
        <x-global::forms.text-input
            type="email"
            name="email"
            class="form-control"
            :value="old('email')"
            caption="{{ __('label.email') }}"
            placeholder="{{ __('label.email') }}"
        /><br />
    
        <x-global::forms.text-input
            type="password"
            name="password"
            class="form-control"
            caption="{{ __('label.password') }}"
            placeholder="{{ __('label.password') }}"
        /><br /><br />
    
        <h3 class="subtitle">{{ __('subtitles.user_info') }}</h3>
    
        <x-global::forms.text-input
            type="text"
            name="firstname"
            class="form-control"
            :value="old('firstname')"
            caption="{{ __('label.firstname') }}"
            placeholder="{{ __('label.firstname') }}"
        /><br />
    
        <x-global::forms.text-input
            type="text"
            name="lastname"
            class="form-control"
            :value="old('lastname')"
            caption="{{ __('label.lastname') }}"
            placeholder="{{ __('label.lastname') }}"
        /><br />
    
        <x-global::forms.text-input
            type="text"
            name="company"
            class="form-control"
            :value="old('company')"
            caption="{{ __('label.company_name') }}"
            placeholder="{{ __('label.company_name') }}"
        /><br /><br />
    
        <input type="hidden" name="install" value="Install" />
    
        <p>
            <x-global::forms.button
                type="submit"
                name="installAction"
                class="btn btn-primary"
                :value="__('buttons.install')"
                onClick="this.form.submit(); this.disabled=true; this.value='{{ __('buttons.install') }}';"
            >
                {{ __('buttons.install') }}
            </x-global::forms.button>
        </p>
    </form>
    
    <br />
            <br /><br />
        <input type="hidden" name="install" value="Install" />
        <p>
            <x-global::forms.button type="submit" name="installAction" onClick="this.form.submit(); this.disabled=true; this.value='{{ __('buttons.install') }}';">
                {{ __('buttons.install') }}
            </x-global::forms.button>
        </p>
    </form>

</div>

@endsection
