

<?php
$currentSprint = $tpl->get('sprint');
?>

<div class="center padding-lg">

    <div class="row">
        <div class="col-md-12">
            <h3 class="primaryColor"><?php echo $tpl->__('headlines.help') ?></h3><br /><br />
            <?php echo $tpl->__('text.not_found_helper_content') ?>
            <br /><br />
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">
            <p>
             </p>
            <a href="javascript:void(0);"  onclick="jQuery.nmTop().close()"><?php echo $tpl->__('links.close') ?></a><br />
        </div>
    </div>


</div>
