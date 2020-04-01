<?php
defined('RESTRICTED') or die('Restricted access');

$milestones = $this->get('milestones');

?>

<?php
if(isset($_SESSION['userdata']['settings']['views']['roadmap'])){
    $roadmapView = $_SESSION['userdata']['settings']['views']['roadmap'];
}else{
    $roadmapView = "Month";
}
?>
<div class="pageheader">
    <div class="pageicon"><span class="fa fa-sliders"></span></div>
    <div class="pagetitle">
        <h5><?php $this->e($_SESSION['currentProjectClient']." // ". $_SESSION['currentProjectName']); ?></h5>
        <h1><?=$this->__("headline.milestones"); ?></h1>
    </div>
</div><!--pageheader-->


<div class="maincontent">
    <div class="maincontentinner">

        <?php echo $this->displayNotification(); ?>

        <div class="row">
            <div class="col-md-6">
                <a href="<?=BASE_URL ?>/tickets/editMilestone" class="milestoneModal btn btn-primary"><?=$this->__("links.add_milestone"); ?></a>
            </div>
            <div class="col-md-6">
                <div class="pull-right">
                    <div class="btn-group dropRight">

                        <?php
                            $currentView = "";
                            if($roadmapView == 'Day') {
                                $currentView = $this->__("buttons.day");
                            }elseif($roadmapView == 'Week') {
                                $currentView = $this->__("buttons.week");
                            }elseif($roadmapView == 'Month') {
                                $currentView = $this->__("buttons.month");
                            }
                        ?>
                        <button class="btn dropdown-toggle" data-toggle="dropdown"><?=$this->__("buttons.timeframe"); ?>: <span class="viewText"><?=$currentView; ?></span><span class="caret"></span></button>
                        <ul class="dropdown-menu" id="ganttTimeControl">
                            <li><a href="javascript:void(0);" data-value="Day" class="<?php if($roadmapView == 'Day') echo "active";?>"> <?=$this->__("buttons.day"); ?></a></li>
                            <li><a href="javascript:void(0);" data-value="Week" class="<?php if($roadmapView == 'Week') echo "active";?>"><?=$this->__("buttons.week"); ?></a></li>
                            <li><a href="javascript:void(0);" data-value="Month" class="<?php if($roadmapView == 'Month') echo "active";?>"><?=$this->__("buttons.month"); ?></a></li>
                        </ul>
                    </div>

                    <div class="btn-group viewDropDown">
                        <button class="btn dropdown-toggle" data-toggle="dropdown"><?=$this->__("links.gantt_view") ?> <?=$this->__("links.view") ?></button>
                        <ul class="dropdown-menu">
                            <li><a href="<?=BASE_URL ?>/tickets/roadmap" class="active"><?=$this->__("links.gantt_view") ?></a></li>
                            <li><a href="<?=BASE_URL ?>/tickets/showAllMilestones" ><?=$this->__("links.table") ?></a></li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>

        <?php
        if(count($milestones) == 0) {
            echo"<div class='empty' id='emptySprint' style='text-align:center;'>";
            echo"<div style='width:50%' class='svgContainer'>";
            echo file_get_contents(ROOT."/images/svg/undraw_adjustments_p22m.svg");
            echo"</div>";
            echo"
            <h4>".$this->__("headlines.no_milestones")."<br/>
            
            <br />
            <a href=\"".BASE_URL."/tickets/editMilestone\" class=\"milestoneModal addCanvasLink btn btn-primary\">".$this->__("links.add_milestone")."</a></h4></div>";

        }
        ?>
        <div class="gantt-container" style="height:100%; overflow: auto;">
            <svg id="gantt"></svg>
        </div>

    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function(){
    <?php if(isset($_SESSION['userdata']['settings']["modals"]["roadmap"]) === false || $_SESSION['userdata']['settings']["modals"]["roadmap"] == 0){     ?>
    leantime.helperController.showHelperModal("roadmap");
    <?php
    //Only show once per session
    $_SESSION['userdata']['settings']["modals"]["roadmap"] = 1;
    } ?>

    <?php if(isset($_GET['showMilestoneModal'])) {

        if($_GET['showMilestoneModal'] == "") {
            $modalUrl = "";
        }else{
            $modalUrl = "/".(int)$_GET['showMilestoneModal'];
        }
        ?>

        leantime.ticketsController.openMilestoneModalManually("<?=BASE_URL ?>/tickets/editMilestone<?php echo $modalUrl; ?>");
        window.history.pushState({},document.title, '<?=BASE_URL ?>/tickets/roadmap');

    <?php } ?>


});

    <?php if(count($milestones) > 0) {?>
        var tasks = [

            <?php foreach($milestones as $mlst){


                echo"{
                    id :'".$mlst->id."',
                    name :".json_encode("".$mlst->headline." (".$mlst->percentDone."% Done)").",
                    start :'".(($mlst->editFrom != '0000-00-00 00:00:00' && substr($mlst->editFrom, 0, 10) != '1969-12-31')? $mlst->editFrom :  date('Y-m-d'))."',
                    end :'".(($mlst->editTo != '0000-00-00 00:00:00' && substr($mlst->editTo, 0, 10) != '1969-12-31') ? $mlst->editTo :  date('Y-m-d', strtotime("+1 day", time())))."',
                    progress :'".$mlst->percentDone."',
                    dependencies :'".($mlst->dependingTicketId != 0 ? $mlst->dependingTicketId : '')."',
                    custom_class :'',
                    color: '".$mlst->tags."',
                    bgColor: '".$mlst->tags."',
                   
                },";
            }
            ?>
        ];

        leantime.ticketsController.initGanttChart(tasks, '<?=$roadmapView; ?>');
    <?php } ?>

</script>

