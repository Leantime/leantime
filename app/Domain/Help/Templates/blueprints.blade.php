<?php
$currentSprint = $tpl->get('sprint');
?>

<div class="center padding-lg">

    <div class="row">
        <div class="col-md-12">
            <div style='width:50%' class='svgContainer'>
                <?php    echo file_get_contents(ROOT . "/dist/images/svg/undraw_design_data_khdb.svg");
                echo"</div>";?>
            <h1>Define your projects with ease</h1><br />
            <p>Blueprints are your chance to make sense of all the data. Leantime has a variety of tools and canvases to define your project background via Business Model Canvases, SWOT Analysis or Empathy Maps.<br /><br />
            If you don't know where to start we suggest you create a "Project Value Canvas". This canvas will answer the most important questions of your project:

                Who is your customer? <br />
                What problem are you solving?<br />
                What is your solution?<br />
                What benefit does your solution offer over your competitors<br />
            </p>
            <br /><br />
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">

            <a href="{{ BASE_URL }}/valuecanvas/showCanvas"  class="btn btn-primary">Create a Project Value Canvas</a><br />
            <a href="javascript:void(0);" onclick="leantime.helperController.hideAndKeepHidden('blueprints')">{{ __("links.close_dont_show_again") }}</a>
        </div>
    </div>


</div>
