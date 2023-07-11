
<?php
$availableStrategyBoards = $this->get('availableStrategyBoards');
$canvasProgress = $this->get('canvasProgress');
?>


<div class="pageheader">
    <div class="pageicon"><span class="fa-solid fa-chess"></span></div>
    <div class="pagetitle">
        <h1><?=$this->__('headlines.blueprints') ?></h1>
    </div>
</div>

<?php echo $this->displayNotification(); ?>

<div class="maincontent">

    <div class="row">
        <div class="col-md-12">
            <div class="maincontentinner">
                <h5 class="subtitle">Jump right back in</h5>
                <div class="row">
                <?php foreach ($this->get('recentProgressCanvas') as $board) {?>
                    <div class="col-md-3">
                        <div class="profileBox">
                            <div class="commentImage icon">
                                <i class="<?=$board['icon']?>"></i>
                            </div>
                            <span class="userName">
                                    <small><?=$this->__($board['name']) ?> (<?=$board['count']?>)</small><br />

                                    <a href="<?=BASE_URL . '/' . $board['module'] . "/showCanvas/".$board['lastCanvasId']?>">
                                        <?=$this->escape($board['lastTitle']) ?>
                                    </a><br />
                                <small><?=$this->__('label.last_updated')?> <?=$this->getFormattedDateString($board['lastUpdate'])?> <?=$this->getFormattedTimeString($board['lastUpdate'])?></p>
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
                                    <span class="sr-only"><?=sprintf($this->__("text.percent_complete"), $percentDone)?></span>
                                </div>
                            </div>
                            <?=sprintf($this->__("text.percent_complete"), $percentDone)?>


                        </div>
                    </div>
                <?php } ?>

                <?php if (!is_array($this->get('recentProgressCanvas')) || count($this->get('recentProgressCanvas')) == 0){

                    echo "<div class='col-md-12'><br /><br /><div class='center'>";

                    echo "<div style='width:30%' class='svgContainer'>";
                    echo file_get_contents(ROOT . "/dist/images/svg/undraw_design_data_khdb.svg");
                    echo "</div>";

                    echo"<h3>" . $this->__("headline.no_blueprints_yet") . "</h3>";
                    echo "<br />" . $this->__("text.no_blueprints_yet");
                    echo "<br /><a href='".BASE_URL."/valuecanvas/showCanvas' class='btn btn-primary'>" . $this->__("button.start_here_project_value") ."</a>";

                    echo"</div></div>";

                     } ?>
                </div>





                <?php
                /*

                if ($this->get('recentlyUpdatedCanvas') !== false && count($this->get('recentlyUpdatedCanvas')) > 0) { ?>
                    <ul class="sortableTicketList" id="lastUpdatedCanvasList" >
                        <?php foreach ($this->get('recentlyUpdatedCanvas') as $canvas) { ?>
                            <li style="margin-bottom:10px;">

                                <div class="col-md-12 ticketBox fixed" style="padding:10px; margin-bottom:0px;">
                                    <small><?=$this->__("label." . $canvas['type'])?></small><br />
                                    <h3>
                                        <a href="<?=BASE_URL?>/<?=$canvas['type'] ?>/showCanvas/<?=$canvas['id']?>">
                                            <?php $this->e($canvas['title'])?>
                                        </a>
                                    </h3>
                                    <p><?=$this->__('label.last_updated')?> <?=$this->getFormattedDateString($canvas['modified'])?> <?=$this->getFormattedTimeString($canvas['modified'])?></p>
                                </div>

                            </li>
                        <?php } ?>
                    </ul>
                <?php } else {
                    echo "<br /><br /><div class='center'>";

                    echo "<div style='width:30%' class='svgContainer'>";
                    echo file_get_contents(ROOT . "/dist/images/svg/undraw_design_data_khdb.svg");
                    echo "</div>";

                    echo"<h4>" . $this->__("headlines.so_empty") . "</h4>";
                    echo "<br />" . $this->__("text.no_canvas");


                    echo"</div>";
                } */?>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="maincontentinner">
                <h5 class="accordionTitle" id="accordion_link_other">
                    <a href="javascript:void(0)" class="accordion-toggle" id="accordion_toggle_other" onclick="accordionToggle('other');">
                        <i class="fa fa-angle-down"></i> Templates
                    </a>
                </h5>
                <p style="padding-left:19px;"><?=$this->__('description.other_tools') ?></p>
                <div id="accordion_other" class="row teamBox" style="padding-left:19px;">


                    <?php foreach ($this->get('otherBoards') as $board) {
                        if(!isset($board["visible"]) || $board["visible"] === 1) {
                        ?>
                        <div class="col-md-3">
                            <div class="profileBox" style="min-height: 125px;">
                                <div class="commentImage icon">
                                    <i class="<?=$board['icon']?>"></i>
                                </div>
                                <span class="userName">
                            <a href="<?=BASE_URL . '/' . $board['module'] . "/showCanvas" ?>">
                                <?=$this->__($board['name']) ?>
                            </a>
                        </span>
                                <?=$this->__($board['description']) ?>
                                <div class="clearall"></div>


                            </div>
                        </div>
                    <?php }
                    } ?>
                </div>


            </div>
        </div>
    </div>

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

