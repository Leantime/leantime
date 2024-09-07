@extends($layout)

@section('content')
$user = $tpl->get('user');
?>

<div class="pageheader">
    <div class="pageicon"><i class="fa-solid fa-key"></i></div>
    <div class="pagetitle">
        <h5><?php echo $tpl->__('label.administration') ?></h5>
        <h1><?php echo $tpl->__('headlines.delete_key'); ?></h1>
    </div>
</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">

        @displayNotification()

        <h5 class="subtitle"><?php echo $tpl->__("subtitles.delete_key"); ?></h5>

            <form method="post">
                <input type="hidden" name="<?=session("formTokenName")?>" value="<?=session("formTokenValue")?>" />
                <p><?php echo $tpl->__('text.confirm_key_deletion'); ?></p><br />
                <input type="submit" value="<?php echo $tpl->__('buttons.yes_delete'); ?>" name="del" class="button" />
                <a class="btn btn-primary" href="<?=BASE_URL ?>/setting/editCompanySettings/#apiKeys"><?php echo $tpl->__('buttons.back'); ?></a>
            </form>

    </div>
</div>
