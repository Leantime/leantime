@extends($layout)

@section('content')

<?php
?>

<div class="pageheader">
    <div class="pageicon"><span class="fa fa-lock"></span></div>
    <div class="pagetitle">
        <h1>{{ __("label.twoFA") }}</h1>
    </div>
</div>

<div class="maincontent">
    <div class="maincontentinner">

        @displayNotification()

        <div class="row-fluid">
            <div class="span12">

                    <h3>{{ __("label.twoFA_setup") }}</h3>
                <br />
                        <div class="center">

                        <?php if (!$tpl->get('twoFAEnabled')) { ?>
                            <h5>1. {{ __("text.twoFA_qr") }}</h5>
                            <img src="<?php echo $tpl->get("qrData"); ?>"/><br />
                            Secret: <p><?php echo $tpl->get("secret"); ?></p>
                            <form action="" method="post" class='stdform'>
                                <h5>2. {{ __("text.twoFA_verify_code") }}</h5>
                                <p>
                                    <span>{{ __("label.twoFACode_short") }}:</span>
                                    <input type="text" class="input" name="twoFACode" id="twoFACode"/><br/>
                                </p>

                                <input type="hidden" name="secret" value="<?php echo $tpl->get("secret"); ?>" />
                                <br/>
                                <p class='stdformbutton'>
                                    <x-global::forms.button type="submit" name="save" id="save" class="button">
                                        {{ __('buttons.save') }}
                                    </x-global::forms.button>                                    
                                </p>
                            </form>
                        <?php } else { ?>
                            <form action="" method="post" class='stdform'>
                                <h5>{{ __("text.twoFA_already_enabled") }}</h5>
                                <input type="hidden" name="<?=session("formTokenName")?>" value="<?=session("formTokenValue")?>" />
                                <p class='stdformbutton'>
                                    <x-global::forms.button type="submit" name="disable" id="disable" class="button">
                                        {{ __('buttons.remove') }}
                                    </x-global::forms.button>
                                    
                                    <x-global::forms.button tag="a" href="{{ BASE_URL }}/users/editOwn">
                                        {{ __('buttons.back') }}
                                    </x-global::forms.button>
                                    
                                </p>
                            </form>
                        <?php } ?>
                        </div>

            </div>
        </div>
    </div>
</div>
