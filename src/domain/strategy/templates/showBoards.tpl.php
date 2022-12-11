
<?php
$availableStrategyBoards = $this->get('availableStrategyBoards');
$canvasProgress = $this->get('canvasProgress');
?>


<div class="pageheader">
    <div class="pageicon"><span class="fa fa-map-location"></span></div>
    <div class="pagetitle">
        <h1><?=$this->__('headlines.project_path') ?></h1>
    </div>
</div>

<?php echo $this->displayNotification(); ?>

<div class="maincontent">

    <div class="row">
        <div class="col-md-8">
            <div class="maincontentinner">
                <h5 class="accordionTitle" id="accordion_link_lvl1">
                    <a href="javascript:void(0)" class="accordion-toggle" id="accordion_toggle_lvl1" onclick="accordionToggle('lvl1');">
                        <i class="fa fa-angle-down"></i> <?=$this->__('label.level1_validate') ?>
                    </a>
                </h5>
                <p style="padding-left:19px;"><?=$this->__('description.level1_validate') ?></p>
                <div id="accordion_lvl1" class="row teamBox" style="padding-left:19px; padding-right:19px;">
                    <?php foreach($this->get('level1Boards') as $board){?>

                        <div class="col-md-6">
                            <div class="profileBox">
                                <div class="commentImage icon">
                                    <i class="<?=$board['icon']?>"></i>
                                </div>
                                <span class="userName">
                            <a href="<?=BASE_URL.'/'.$board['module']."/showCanvas" ?>">
                                <?=$this->__($board['name']) ?>
                            </a>
                        </span>
                                <?=$this->__($board['description']) ?>
                                <div class="clearall"></div>
                                <?php
                                $percentDone = 0;
                                if(isset($canvasProgress[$board['module']])) {
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
                </div>
            </div>
            <div class="maincontentinner">
                <h5 class="accordionTitle" id="accordion_link_lvl2">
                    <a href="javascript:void(0)" class="accordion-toggle" id="accordion_toggle_lvl2" onclick="accordionToggle('lvl2');">
                        <i class="fa fa-angle-down"></i> <?=$this->__('label.level2_define') ?>
                    </a>
                </h5>
                <p style="padding-left:19px;"><?=$this->__('description.level2_define') ?></p>
                <div id="accordion_lvl2" class="row teamBox" style="padding-left:19px; padding-right:19px;">
                    <?php foreach($this->get('level2Boards') as $board){?>

                        <div class="col-md-6">
                            <div class="profileBox">
                                <div class="commentImage icon">
                                    <i class="<?=$board['icon']?>"></i>
                                </div>
                                <span class="userName">
                            <a href="<?=BASE_URL.'/'.$board['module']."/showCanvas" ?>">
                                <?=$this->__($board['name']) ?>
                            </a>
                        </span>
                                <?=$this->__($board['description']) ?>
                                <div class="clearall"></div>

                                <?php
                                $percentDone = 0;
                                if(isset($canvasProgress[$board['module']])) {
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
                </div>
            </div>
            <div class="maincontentinner">
                <h5 class="accordionTitle" id="accordion_link_lvl3">
                    <a href="javascript:void(0)" class="accordion-toggle" id="accordion_toggle_lvl3" onclick="accordionToggle('lvl3');">
                        <i class="fa fa-angle-down"></i> <?=$this->__('label.level3_plan') ?>
                    </a>
                </h5>
                <p style="padding-left:19px;"><?=$this->__('description.level3_plan') ?></p>
                <div id="accordion_lvl3" class="row teamBox" style="padding-left:19px; padding-right:19px;">
                    <?php foreach($this->get('level3Boards') as $board){?>

                        <div class="col-md-6">
                            <div class="profileBox">
                                <div class="commentImage icon">
                                    <i class="<?=$board['icon']?>"></i>
                                </div>
                                <span class="userName">
                            <a href="<?=BASE_URL.'/'.$board['module']."/showCanvas" ?>">
                                <?=$this->__($board['name']) ?>
                            </a>
                        </span>
                                <?=$this->__($board['description']) ?>
                                <div class="clearall"></div>

                                <?php
                                $percentDone = 0;
                                if(isset($canvasProgress[$board['module']])) {
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
                </div>
            </div>
            <div class="maincontentinner">
                <h5 class="accordionTitle" id="accordion_link_other">
                    <a href="javascript:void(0)" class="accordion-toggle" id="accordion_toggle_other" onclick="accordionToggle('other');">
                        <i class="fa fa-angle-right"></i> <?=$this->__('label.other_tools') ?>
                    </a>
                </h5>
                <p style="padding-left:19px;"><?=$this->__('description.other_tools') ?></p>
                <div id="accordion_other" class="row teamBox" style="display:none; padding-left:19px;">
                    <?php foreach($this->get('otherBoards') as $board){?>

                        <div class="col-md-4">
                            <div class="profileBox">
                                <div class="commentImage icon">
                                    <i class="<?=$board['icon']?>"></i>
                                </div>
                                <span class="userName">
                            <a href="<?=BASE_URL.'/'.$board['module']."/showCanvas" ?>">
                                <?=$this->__($board['name']) ?>
                            </a>
                        </span>
                                <?=$this->__($board['description']) ?>
                                <div class="clearall"></div>

                                <?php
                                $percentDone = 0;
                                if(isset($canvasProgress[$board['module']])) {
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
                </div>


            </div>
        </div>
        <div class="col-md-4">
            <div class="maincontentinner">
                <h5 class="subtitle"><?=$this->__('headlines.recent_updates') ?></h5>

                <?php if($this->get('recentlyUpdatedCanvas') !== false && count($this->get('recentlyUpdatedCanvas'))>0){ ?>
                <ul class="sortableTicketList" id="lastUpdatedCanvasList" >
                    <?php foreach($this->get('recentlyUpdatedCanvas') as $canvas) { ?>
                        <li style="margin-bottom:10px;">

                                <div class="col-md-12 ticketBox fixed" style="padding:10px; margin-bottom:0px;">
                                    <small><?=$this->__("label.".$canvas['type'])?></small><br />
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
                <?php }else{
                    echo "<br /><br /><div class='center'>";

                        echo "<div style='width:30%' class='svgContainer'>";
                            echo file_get_contents(ROOT."/images/svg/undraw_design_data_khdb.svg");
                            echo "</div>";

                        echo"<h4>".$this->__("headlines.so_empty")."</h4>";
                        echo "<br />".$this->__("text.no_canvas");


                        echo"</div>";

                } ?>
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

