<?php
  $currentSprint = $this->get('sprint');
?>

<div class="center padding-lg">

    <div class="row">
        <div class="col-md-12">
            <div style='width:50%' class='svgContainer'>
                <?php    echo file_get_contents(ROOT."/images/svg/undraw_complete_task_u2c3.svg");
                echo"</div>";?><br />
            <h3 class="primaryColor"><?php echo $this->__('headlines.welcome_to_clients_products') ?></h3><br />
            <?php echo $this->__('text.show_clients_helper_content') ?>
            <br /><br />
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">
            <p>
             </p>
            <a href="javascript:void(0);"  onclick="jQuery.nmTop().close()"><?php echo $this->__('links.close') ?></a><br />
            <a href="javascript:void(0);" onclick="leantime.helperController.hideAndKeepHidden('showClients')"><?php echo $this->__('links.close_dont_show_again') ?></a>
        </div>
    </div>


</div>
