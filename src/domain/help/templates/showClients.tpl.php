<?php
  $currentSprint = $this->get('sprint');
?>

<div class="center padding-lg">

    <div class="row">
        <div class="col-md-12">
            <div style='width:50%' class='svgContainer'>
                <?php    echo file_get_contents(ROOT."/images/svg/undraw_complete_task_u2c3.svg");
                echo"</div>";?><br />
            <h3 class="primaryColor">Welcome to Clients / Products!</h3><br />
            <p>Clients/Products organize your Projects into categories and allow you to isolate different groups.<br /><br /></p>
            <p>As a consultant, you work with clients already and this would be the place to organize them.</p>
            <p>If you are a Product Team you can use this section to organize and isolate your departments (marketing, sales, operations etc).</p>
            <br /><br />
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">
            <p>
             </p>
            <a href="javascript:void(0);"  onclick="jQuery.nmTop().close()"><i class="fa fa-close"></i> Close</a><br />
            <a href="javascript:void(0);" onclick="leantime.helperController.hideAndKeepHidden('showClients')">Close and don't show this screen again</a>
        </div>
    </div>


</div>
