<?php
  $currentLabel = $this->get('currentLabel');
?>

<h4 class="widgettitle title-light"><i class="fas fa-edit"></i> Edit Label</h4>

<?php echo $this->displayNotification();

?>

<form class="editLabelModal" method="post" action="<?=BASE_URL ?>/setting/editBoxLabel?module=<?php echo $_GET['module']?>&label=<?php echo $_GET['label']?>" style="min-width: 320px;">

    <?php if($currentLabel !== false) {?>
    <label>Label</label>
    <input type="text" name="newLabel" value="<?php echo $currentLabel?>" /><br />


    <div class="row">
        <div class="col-md-6">
            <input type="submit" value="Save"/>
        </div>

    </div>
    <?php } else {?>
        <div class="align-center">
            <i class="fa fa-cogs" style="font-size:48px"></i>
            <h4>Premium Feature!</h4> <br />We would love to show you this page, but this is a premium feature. <br />Sign for a paid subscription to change the labels of the board.
            <br /><br /><a href="<?=BASE_URL ?>/billing/subscriptions" class="btn btn-primary">Sign Up for the Emerging Plan</a>
        </div>
    <?php } ?>

</form>

