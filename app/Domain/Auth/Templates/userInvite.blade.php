@extends($layout)

@section('content')

<?php
$user = $tpl->get("user");
?>
<div class="projectSteps">
    <div class="progressWrapper">
        <div class="progress">
            <div
                id="progressChecklistBar"
                class="progress-bar progress-bar-success tx-transition"
                role="progressbar"
                aria-valuenow="0"
                aria-valuemin="0"
                aria-valuemax="100"
                style="width: 12%"
            ><span class="sr-only">12%</span></div>
        </div>


        <div class="step current" style="left: 12%;">
            <a href="javascript:void(0)" data-toggle="dropdown" class="dropdown-toggle">
                <span class="innerCircle"></span>
                <span class="title">
                           <i class="fa-regular fa-circle"></i>
                            Step 1
                        </span>
            </a>
        </div>

        <div class="step " style="left: 37%;">
            <a href="javascript:void(0)" data-toggle="dropdown" class="dropdown-toggle">
                <span class="innerCircle"></span>
                <span class="title">
                           <i class="fa-regular fa-circle"></i>
                            Step 2
                        </span>
            </a>
        </div>

        <div class="step " style="left: 62%;">
            <a href="javascript:void(0)" data-toggle="dropdown" class="dropdown-toggle">
                <span class="innerCircle"></span>
                <span class="title">
                           <i class="fa-regular fa-circle"></i>
                        Step 3
                        </span>
            </a>
        </div>

        <div class="step " style="left: 88%;">
            <a href="javascript:void(0)" data-toggle="dropdown" class="dropdown-toggle">
                <span class="innerCircle"></span>
                <span class="title">
                           <i class="fa-regular fa-circle"></i>
                        Step 4
                        </span>
            </a>
        </div>

    </div>
</div>
<br /><br /><br />


<h2><?php echo $tpl->language->__("headlines.set_up_your_account"); ?></h2>

<?php $tpl->dispatchTplEvent('afterPageHeaderClose'); ?>
<div class="regcontent">
    <?php $tpl->dispatchTplEvent('afterRegcontentOpen'); ?>

    <form id="resetPassword" action="" method="post">
        <?php $tpl->dispatchTplEvent('afterFormOpen'); ?>

        <?php echo $tpl->displayInlineNotification(); ?>

        <input type="hidden" name="step" value="1"/>

        <div class="">
            <input type="text" name="firstname" style="margin-bottom:15px" id="firstname" placeholder="<?php echo $tpl->language->__("input.placeholders.firstname"); ?>" value="<?=$tpl->escape($user['firstname']); ?>" />

        </div>
        <div class="">
            <input type="text" name="lastname" id="lastname" style="margin-bottom:15px" placeholder="<?php echo $tpl->language->__("input.placeholders.lastname"); ?>" value="<?=$tpl->escape($user['lastname']); ?>" />
        </div>
        <div class="">
            <input type="text" name="jobTitle" id="jobTitle" style="margin-bottom:15px" placeholder="<?php echo $tpl->language->__("input.placeholders.jobtitle"); ?>" value="<?=$tpl->escape($user['jobTitle']); ?>" />
        </div>
        <div class="">
            <input type="password" name="password" id="password" style="margin-bottom:15px" placeholder="<?php echo $tpl->language->__("input.placeholders.enter_new_password"); ?>" />
            <span id="pwStrength" style="width:100%;"></span>
        </div>
        <div class=" ">
            <input type="password" name="password2" id="password2" style="margin-bottom:15px" placeholder="<?php echo $tpl->language->__("input.placeholders.confirm_password"); ?>" />
        </div>
        <small><?=$tpl->__('label.passwordRequirements') ?></small><br /><br />
        <div class="">
            <input type="hidden" name="saveAccount" value="1" />
            <?php $tpl->dispatchTplEvent('beforeSubmitButton'); ?>
            <input type="submit" name="createAccount" value="<?php echo $tpl->language->__("buttons.next"); ?>" />

        </div>
        <?php $tpl->dispatchTplEvent('beforeFormClose'); ?>
    </form>
    <?php $tpl->dispatchTplEvent('beforeRegcontentClose'); ?>
</div>

<script>
    leantime.usersController.checkPWStrength('password');
</script>

@endsection
