@extends($layout)

@section('content')

@include("auth::partials.onboardingProgress", ['percentComplete' => 12, 'current' => 'account', 'completed' => []])

<h2>{{ __('titles.account_details') }}</h2>

<?php $tpl->dispatchTplEvent('afterPageHeaderClose'); ?>
<div class="regcontent">
    <?php $tpl->dispatchTplEvent('afterRegcontentOpen'); ?>

    <form id="resetPassword" action="" method="post">
        <?php $tpl->dispatchTplEvent('afterFormOpen'); ?>

        <?php echo $tpl->displayInlineNotification(); ?>

        <input type="hidden" name="step" value="1"/>

        <div class="">
            <label for="name"><?php echo $tpl->language->__("label.name"); ?></label>
            <x-global::forms.input name="name" style="margin-bottom:15px" id="name" placeholder="<?php echo $tpl->language->__("input.placeholders.name"); ?>" value="<?=$tpl->escape($user['firstname']); ?>" />
        </div>
        <div class="">
            <label for="jobTitle"><?php echo $tpl->language->__("label.role_or_title"); ?></label>
            <x-global::forms.input name="jobTitle" id="jobTitle" style="margin-bottom:15px" placeholder="<?php echo $tpl->language->__("input.placeholders.jobtitle"); ?>" value="<?=$tpl->escape($user['jobTitle']); ?>" />

        </div>
        <div class="">
            <label for="password"><?php echo $tpl->language->__("label.password"); ?></label>
            <x-global::forms.input type="password" name="password" autocomplete="off" id="password" style="margin-bottom:15px" placeholder="<?php echo $tpl->language->__("input.placeholders.enter_new_password"); ?>" />
            <span id="pwStrength" style="width:100%;"></span>
        </div>
        <small><?=$tpl->__('label.passwordRequirements') ?></small><br /><br />
        <div class="">
            <input type="hidden" name="saveAccount" value="1" />
            <?php $tpl->dispatchTplEvent('beforeSubmitButton'); ?>
            <div class="tw:text-right">
                <x-global::button submit type="primary" name="createAccount" class="tw:w-auto" style="width:auto">{{ __("buttons.next") }}</x-global::button>
            </div>

        </div>
        <?php $tpl->dispatchTplEvent('beforeFormClose'); ?>
    </form>
    <?php $tpl->dispatchTplEvent('beforeRegcontentClose'); ?>
</div>

<script>
    leantime.usersController.checkPWStrength('password');
</script>

@endsection
