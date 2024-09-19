@extends($layout)

@section('content')

<?php
$redirectUrl = $tpl->get("redirectUrl");
?>

<div class="pageheader">
    <div class="pagetitle">
        <h1><?php echo $tpl->language->__("headlines.twoFA_login"); ?></h1>
    </div>
</div>
<div class="regcontent">
    <form id="login" action="<?php echo BASE_URL . "/twoFA/verify" ?>" method="post">
        <input type="hidden" name="redirectUrl" value="<?php echo $redirectUrl ?>"/>

        <?php echo $tpl->displayInlineNotification(); ?>

        <div>
            <x-global::forms.text-input 
                type="text" 
                name="twoFA_code" 
                id="twoFA_code" 
                class="form-control" 
                placeholder="{!! $tpl->language->__('label.twoFACode') !!}" 
                value="" 
                autofocus 
            />
        </div>
        
        <div>
            <div class="forgotPwContainer">
                <x-global::forms.button 
                    tag="a"
                    href="{{ BASE_URL }}/auth/logout"
                    class="forgotPw"
                >
                    {!! $tpl->language->__('menu.sign_out') !!}
                </x-global::forms.button>
            </div>
        
            <x-global::forms.button 
                type="submit" 
                name="login"
                class="btn btn-primary"
            >
                {!! $tpl->language->__('buttons.login') !!}
            </x-global::forms.button>
        </div>
        
    </form>
</div>
