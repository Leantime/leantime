@extends($layout)

@section('content')

<?php
$redirectUrl = $tpl->get('redirectUrl');
?>

<?php $tpl->dispatchTplEvent('beforePageHeaderOpen'); ?>
<div class="pageheader">
    <?php $tpl->dispatchTplEvent('afterPageHeaderOpen'); ?>
    <div class="pagetitle">
        <h1><?php echo $tpl->language->__("headlines.login"); ?></h1>
    </div>
    <?php $tpl->dispatchTplEvent('beforePageHeaderClose'); ?>
</div>
<?php $tpl->dispatchTplEvent('afterPageHeaderClose'); ?>

<div class="regcontent">
    <?php $tpl->dispatchTplEvent('afterRegcontentOpen'); ?>
    <?php echo $tpl->displayInlineNotification(); ?>

    <?php if (false === $tpl->get('noLoginForm')) { ?>
        <form id="login" action="<?=BASE_URL . "/auth/login"?>" method="post">
            <?php $tpl->dispatchTplEvent('afterFormOpen'); ?>
        <input type="hidden" name="redirectUrl" value="<?php echo $redirectUrl; ?>" />

        <div class="">
            <x-global::forms.text-input
                type="text"
                name="username"
                id="username"
                labelText="Email"
                placeholder="{{ $tpl->language->__($tpl->get('inputPlaceholder')) }}"
                value=""
                cornerShape="full"
            />
        </div>

        <div class="">
            <x-global::forms.text-input
                type="password"
                name="password"
                id="password"
                labelText="Password"
                placeholder="{{ $tpl->language->__('input.placeholders.enter_password') }}"
                value=""
                cornerShape="full"
            />

            <div class="forgotPwContainer">
                <a href="{{ BASE_URL }}/auth/resetPw" class="forgotPw">
                    {{ $tpl->language->__('links.forgot_password') }}
                </a>
            </div>
        </div>

        <?php $tpl->dispatchTplEvent('beforeSubmitButton'); ?>

        <div class="">
            <x-global::forms.button type="submit" contentRole="primary" name="login" cornerShape="full" :takeFullWidth="true">
                {{ $tpl->language->__('buttons.login') }}
            </x-global::forms.button>
        </div>


        <?php $tpl->dispatchTplEvent('beforeFormClose'); ?>

    </form>
    <?php } else {
        echo ($tpl->language->__("text.no_login_form"));
        ?><br /><br />
    <?php }// if disableLoginForm ?>
    <?php if ($tpl->get('oidcEnabled')) { ?>
        <?php $tpl->dispatchTplEvent('beforeOidcButton'); ?>
        <div class="">
            <br /><center class="uppercase"><?php echo $tpl->language->__("label.or"); ?></center><br />
            
            <x-global::forms.button onclick="window.location.href = '{{ BASE_URL }}/oidc/login'" contentRole="primary" name="oidclogin" cornerShape="full" :takeFullWidth="true">
                {{ $tpl->language->__('buttons.oidclogin') }}
            </x-global::forms.button>
        </div>
    <?php } ?>
    <?php $tpl->dispatchTplEvent('beforeRegcontentClose'); ?>
</div>

@endsection
