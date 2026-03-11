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
            <label for="name"><?php echo $tpl->language->__('label.name'); ?></label>
            <x-globals::forms.text-input name="name" class="tw:mb-4" id="name" placeholder="{{ $tpl->language->__('input.placeholders.name') }}" :value="$tpl->escape($user['firstname'])" />
        </div>
        <div class="">
            <label for="jobTitle"><?php echo $tpl->language->__('label.role_or_title'); ?></label>
            <x-globals::forms.text-input name="jobTitle" id="jobTitle" class="tw:mb-4" placeholder="{{ $tpl->language->__('input.placeholders.jobtitle') }}" :value="$tpl->escape($user['jobTitle'])" />

        </div>
        <div class="">
            <label for="password"><?php echo $tpl->language->__('label.password'); ?></label>
            <x-globals::forms.text-input type="password" name="password" autocomplete="off" id="password" class="tw:mb-4" placeholder="<?php echo $tpl->language->__('input.placeholders.enter_new_password'); ?>" />
            <span id="pwStrength" class="tw:w-full tw:block"></span>
        </div>
        <small><?= $tpl->__('label.passwordRequirements') ?></small><br /><br />
        <div class="">
            <input type="hidden" name="saveAccount" value="1" />
            <?php $tpl->dispatchTplEvent('beforeSubmitButton'); ?>
            <div class="align-right">
                <x-globals::forms.button :submit="true" contentRole="primary" name="createAccount" class="tw:w-auto">{{ __("buttons.next") }}</x-globals::forms.button>
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
