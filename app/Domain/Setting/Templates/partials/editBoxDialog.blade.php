<?php
$currentLabel = $tpl->get('currentLabel');
?>

<h4 class="widgettitle title-light"><?=$tpl->__("headlines.edit_label")?></h4>

@displayNotification()

<form class="formModal" method="post" action="<?=BASE_URL ?>/setting/editBoxLabel?module=<?php $tpl->e($_GET['module']) ?>&label=<?php  $tpl->e($_GET['label']) ?>">

    <label><?=$tpl->__("label.label")?></label>
    <input type="text" name="newLabel" value="<?php echo $currentLabel; ?>" /><br />

    <div class="row">
        <div class="col-md-6">
            <input type="submit" value="<?=$tpl->__("buttons.save")?>"/>
        </div>

    </div>

</form>

