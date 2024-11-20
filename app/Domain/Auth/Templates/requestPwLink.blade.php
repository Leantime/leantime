@extends($layout)

@section('content')

<?php
$tpl->dispatchTplEvent('beforePageHeaderOpen');
?>
<div class="pageheader">
    <div class="pagetitle">
        <h1><?php echo $tpl->language->__('headlines.reset_password'); ?></h1>
    </div>
</div>
<?php $tpl->dispatchTplEvent('afterPageHeaderClose'); ?>
<div class="regcontent">
    <?php $tpl->dispatchTplEvent('afterRegcontentOpen'); ?>
    <form id="resetPassword" action="" method="post">
        <?php $tpl->dispatchTplEvent('afterFormOpen'); ?>
        <?php echo $tpl->displayInlineNotification(); ?>
        <p><?php echo $tpl->language->__('text.enter_email_address_to_reset'); ?><br /><br /></p>
        <div class="">
            <x-global::forms.text-input
                type="text"
                name="username"
                id="username"
                placeholder="{{ $tpl->language->__('input.placeholders.enter_email') }}"
                variant="fullWidth"
            />
        </div>
        <div class="">
            <div class="forgotPwContainer">
                <a href="{{ BASE_URL }}/auth/login" class="forgotPw"><?php echo $tpl->language->__("links.back_to_login"); ?></a>
            </div>
            <?php $tpl->dispatchTplEvent('beforeSubmitButton'); ?>
            <x-global::forms.button type="submit" name="resetPassword" class="w-full" >
                {{ __('buttons.reset_password') }}
            </x-global::forms.button>
         </div>
        <?php $tpl->dispatchTplEvent('beforeFormClose'); ?>
    </form>
    <?php $tpl->dispatchTplEvent('beforeRegcontentClose'); ?>
</div>

@endsection
