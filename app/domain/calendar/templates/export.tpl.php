<?php
  $url = $this->get('url');
?>

<h4 class="widgettitle title-light"><i class="fa fa-file-export"></i> <?=$this->__('label.ical_export'); ?></h4>

<?php

echo $this->displayNotification();


?>

<form class="formModal" method="post" action="<?=BASE_URL ?>/calendar/export">

    <?php $this->dispatchTplEvent('afterFormOpen'); ?>

    <?php
    echo $this->__('text.ical_export_description');
    echo "<br />";
    ?>

    <?php
    if ($url != false) {
        echo $this->__('text.you_ical_url');
        echo "<br /><input type='text' value='" . $url . "' style='width:100%;'/>";
    } else {
        echo $this->__('text.no_url');
    }
    ?>
    <div class="row">
        <div class="col-md-6">
            <input type="hidden" value="1" name="generateUrl" />

            <?php $this->dispatchTplEvent('beforeSubmitButton'); ?>

            <br /><input type="submit" value="<?=$this->__('buttons.generate_ical_url') ?>"/>

        </div>
        <div class="col-md-6 align-right">
            <?php  if ($url != false) { ?>
                 <a href="<?=BASE_URL ?>/calendar/export?remove=1" class="delete formModal"><i class="fa fa-trash"></i> <?=$this->__('links.remove_access') ?></a>
            <?php } ?>
        </div>
    </div>

    <?php $this->dispatchTplEvent('beforeFormClose'); ?>

</form>

