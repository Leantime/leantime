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
        <h1>Milestones</h1>
    </div>
</div><!--pageheader-->


<div class="maincontent">
    <div class="maincontentinner">

        <?php echo $this->displayNotification(); ?>

        <div class="row">
            <div class="col-md-6">
                <a href="<?=BASE_URL ?>/tickets/editMilestone" class="milestoneModal btn btn-primary"><span class="fa fa-plus"></span> Add Milestone</a>
            </div>
            <div class="col-md-6" style="text-align:right;">

                <div class="btn-group mt-1 mx-auto" role="group">
                    <button type="button" class="btn btn-sm btn-secondary <?php if($roadmapView == 'Day') echo "active";?>">Day</button>
                    <button type="button" class="btn btn-sm btn-secondary <?php if($roadmapView == 'Week') echo "active";?>">Week</button>
                    <button type="button" class="btn btn-sm btn-secondary <?php if($roadmapView == 'Month') echo "active";?>">Month</button>
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
<h4>You don't have any milestones yet<br/>

<br />
<a href='".BASE_URL."/tickets/editMilestone' class=\"milestoneModal addCanvasLink btn btn-primary\"><span class=\"fa fa-map\"></span> Add a Milestone</a></h4></div>";

        }
        ?>
        <div class="gantt-container" style="overflow: auto;">
            <svg id="gantt"></svg>
        </div>

        <?php
        if(isset($_SESSION['tourActive']) === true && $_SESSION['tourActive'] == 1){     ?>
            <p class="align-center"><br /><em>Once you are done adding your Milestone you can jump into your To-Dos</em> <br /><a href="<?=BASE_URL ?>/tickets/showAll/" class="btn btn-primary"><span class="iconfa-pushpin"></span> Backlog</a></p>
        <?php } ?>

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

    leantime.ticketsController.openMilestoneModalManually("<?=BASE_URL?>/tickets/editMilestone<?php echo $modalUrl; ?>");
    window.history.pushState({},document.title, '<?=BASE_URL?>/tickets/roadmap');

    <?php } ?>


});


    <?php if(count($milestones) > 0) {?>
        var tasks = [

            <?php foreach($milestones as $mlst){

                $progress = round($mlst->allTickets ? (($mlst->doneTickets / $mlst->allTickets)* 100) : 0);
                echo"{
                    id :'".$mlst->id."',
                    name :".json_encode("".$mlst->headline." (".$progress."% Done)").",
                    start :'".(($mlst->editFrom != '0000-00-00 00:00:00' && substr($mlst->editFrom, 0, 10) != '1969-12-31')? $mlst->editFrom :  date('Y-m-d'))."',
                    end :'".(($mlst->editTo != '0000-00-00 00:00:00' && substr($mlst->editTo, 0, 10) != '1969-12-31') ? $mlst->editTo :  date('Y-m-d', strtotime("+1 day", time())))."',
                    progress :'".$progress."',
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

