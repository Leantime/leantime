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
        <h3 class="subtitle"><?=$tpl->language->__("subtitles.login_info");?></h3>
        <input type="email" name="email" class="form-control" placeholder="<?=$tpl->language->__("label.email");?>" value=""/><br />
        <input type="password" name="password" class="form-control" placeholder="<?=$tpl->language->__("label.password");?>" />
        <br /><br />
        <h3 class="subtitle"><?=$tpl->language->__("subtitles.user_info");?></h3>
        <x-global::forms.text-input 
        type="text" 
        id="projectName" 
        name="projectname" 
        value="" 
        placeholder="" 
        variant="title" 
        class="w-full" 
    />
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
