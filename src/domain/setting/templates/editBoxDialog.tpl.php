<?php
  $currentLabel = $this->get('currentLabel');
?>

<h4 class="widgettitle title-light"><?=$this->__("headlines.edit_label")?></h4>

<?php
    echo $this->displayNotification();
?>

<form class="editLabelModal" method="post" action="<?=BASE_URL ?>/setting/editBoxLabel?module=<?php echo $_GET['module']?>&label=<?php echo $_GET['label']?>">

    <label><?=$this->__("label.label")?></label>
    <input type="text" name="newLabel" value="<?php echo $currentLabel; ?>" /><br />
    
    <div class="row">
        <div class="col-md-6">
            <input type="submit" value="<?=$this->__("buttons.save")?>"/>
        </div>

    </div>

</form>

