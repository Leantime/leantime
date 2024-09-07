
@extends($layout)

@section('content')

<?php
$availableStrategyBoards = $tpl->get('availableStrategyBoards');
$canvasProgress = $tpl->get('canvasProgress');
?>


<div class="pageheader">
    <div class="pageicon"><span class="fa-solid fa-chess"></span></div>
    <div class="pagetitle">
        <h1><?=$tpl->__('headlines.blueprints') ?></h1>
    </div>
</div>

@displayNotification()

<div class="maincontent">

    <div class="row">
        <div class="col-md-12">
            <div class="maincontentinner">
                <h5 class="subtitle">Jump right back in</h5>
                <div class="row">
                <?php foreach ($tpl->get('recentProgressCanvas') as $board) {?>
                    <div class="col-md-3">
                        <div class="profileBox">
                            <div class="commentImage icon">
                                <i class="<?=$board['icon']?>"></i>
                            </div>
                            <span class="userName">
                                    <small><?=$tpl->__($board['name']) ?> (<?=$board['count']?>)</small><br />

                                    <a href="<?=BASE_URL . '/' . $board['module'] . "/showCanvas/" . $board['lastCanvasId']?>">
                                        <?=$tpl->escape($board['lastTitle']) ?>
                                    </a><br />
                                <small><?=$tpl->__('label.last_updated')?> <?=format($board['lastUpdate'])->date()?> <?=format($board['lastUpdate'])->time()?></p>
                                </small>
                                </span>
                               <div class="clearall"></div>
                            <?php
                            $percentDone = 0;
                            if (isset($canvasProgress[$board['module']])) {
                                $percentDone = round($canvasProgress[$board['module']] * 100);
                            }
                            ?>
                            <br />
                            <div class="progress">
                                <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?php echo $percentDone; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $percentDone; ?>%">
                                    <span class="sr-only"><?=sprintf($tpl->__("text.percent_complete"), $percentDone)?></span>
                                </div>
                            </div>
                            <?=sprintf($tpl->__("text.percent_complete"), $percentDone)?>


                        </div>
                    </div>
                <?php } ?>

                <?php if (!is_array($tpl->get('recentProgressCanvas')) || count($tpl->get('recentProgressCanvas')) == 0) {
                    echo "<div class='col-md-12'><br /><br /><div class='center'>";

                    echo "<div style='width:30%' class='svgContainer'>";
                    echo file_get_contents(ROOT . "/dist/images/svg/undraw_design_data_khdb.svg");
                    echo "</div>";

                    echo"<h3>" . $tpl->__("headline.no_blueprints_yet") . "</h3>";
                    echo "<br />" . $tpl->__("text.no_blueprints_yet");
                    echo "<br /><a href='" . BASE_URL . "/valuecanvas/showCanvas' class='btn btn-primary'>" . $tpl->__("button.start_here_project_value") . "</a>";

                    echo"</div></div>";
                } ?>
                </div>





                <?php
                /*

                if ($tpl->get('recentlyUpdatedCanvas') !== false && count($tpl->get('recentlyUpdatedCanvas')) > 0) { ?>
                    <ul class="sortableTicketList" id="lastUpdatedCanvasList" >
                        <?php foreach ($tpl->get('recentlyUpdatedCanvas') as $canvas) { ?>
                            <li style="margin-bottom:10px;">

                                <div class="col-md-12 ticketBox fixed" style="padding:10px; margin-bottom:0px;">
                                    <small><?=$tpl->__("label." . $canvas['type'])?></small><br />
                                    <h3>
                                        <a href="<?=BASE_URL?>/<?=$canvas['type'] ?>/showCanvas/<?=$canvas['id']?>">
                                            <?php $tpl->e($canvas['title'])?>
                                        </a>
                                    </h3>
                                    <p><?=$tpl->__('label.last_updated')?> <?=format($canvas['modified'])?> <?=format($canvas['modified'])->time()->date?></p>
                                </div>

                            </li>
                        <?php } ?>
                    </ul>
                <?php } else {
                    echo "<br /><br /><div class='center'>";

                    echo "<div style='width:30%' class='svgContainer'>";
                    echo file_get_contents(ROOT . "/dist/images/svg/undraw_design_data_khdb.svg");
                    echo "</div>";

                    echo"<h4>" . $tpl->__("headlines.so_empty") . "</h4>";
                    echo "<br />" . $tpl->__("text.no_canvas");


                    echo"</div>";
                } */?>
            </div>
        </div>
    </div>

    <?php if ($login::userIsAtLeast($roles::$editor)) {?>
    <div class="row">
        <div class="col-md-12">
            <div class="maincontentinner">
                <h5 class="accordionTitle" id="accordion_link_other">
                    <a href="javascript:void(0)" class="accordion-toggle" id="accordion_toggle_other" onclick="accordionToggle('other');">
                        <i class="fa fa-angle-down"></i> Templates
                    </a>
                </h5>
                <p style="padding-left:19px;"><?=$tpl->__('description.other_tools') ?></p>
                <div id="accordion_other" class="row teamBox" style="padding-left:19px;">


                    <?php foreach ($tpl->get('otherBoards') as $board) {
                        if (!isset($board["visible"]) || $board["visible"] === 1) {
                            ?>
                        <div class="col-md-3">
                            <div class="profileBox" style="min-height: 125px;">
                                <div class="commentImage icon">
                                    <i class="<?=$board['icon']?>"></i>
                                </div>
                                <span class="userName">
                            <a href="<?=BASE_URL . '/' . $board['module'] . "/showCanvas" ?>">
                                <?=$tpl->__($board['name']) ?>
                            </a>
                        </span>
                                <?=$tpl->__($board['description']) ?>
                                <div class="clearall"></div>


                            </div>
                        </div>
                        <?php }
                    } ?>
                </div>


            </div>
        </div>
    </div>
    <?php } ?>

</div>




<script>
    function accordionToggle(id) {

        let currentLink = jQuery("#accordion_toggle_"+id).find("i.fa");

        if(currentLink.hasClass("fa-angle-right")){
            currentLink.removeClass("fa-angle-right");
            currentLink.addClass("fa-angle-down");
            jQuery('#accordion_'+id).slideDown("fast");
        }else{
            currentLink.removeClass("fa-angle-down");
            currentLink.addClass("fa-angle-right");
            jQuery('#accordion_'+id).slideUp("fast");
        }

    }
</script>

@endsection
