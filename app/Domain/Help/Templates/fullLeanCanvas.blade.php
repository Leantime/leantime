@extends($layout)

@section('content')

<?php
$currentSprint = $tpl->get('sprint');
?>

<div class="center padding-lg">

    <div class="row">
        <div class="col-md-12">
            <div style='width:50%' class='svgContainer'>
                <?php    echo file_get_contents(ROOT . "/dist/images/svg/undraw_design_data_khdb.svg");
                echo"</div>";?>
            <h1 >{{ __("headlines.welcome_to_research_board") }}</h1><br />
            <p>{{ __("text.full_lean_canvas_helper_content") }}</p>
            <br /><br />
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">
            <p>
             </p>
            <a href="javascript:void(0);"  onclick="jQuery.nmTop().close()">{{ __("links.close") }}</a><br />
            <a href="javascript:void(0);" onclick="leantime.helperController.hideAndKeepHidden('fullLeanCanvas')">{{ __("links.close_dont_show_again") }}</a>
        </div>
    </div>


</div>
