<?php
  $currentSprint = $this->get('sprint');
?>

<div class="center padding-lg">

    <div class="row">
        <div class="col-md-12">
            <span class="bigIcon primaryColor"><span class="iconfa-pushpin"></span></span><br />
            <h3 class="primaryColor">The Kanban Board</h3><br />
            <p>Kanban is the Japanese word for "card". Kanban boards were started by Toyota engineers in the 40s with Lean manufacturing.<br />It's used to visually share information and work progress quickly.<br/>
            Kanban boards are useful to visualize work and status, to help manage active or in progress work, manage work flow and act quickly towards lean improvements.<br/><br />
            Start the tour to see how our Kanban board works or click close to get started!</p>
            <br /><br />
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">
            <p>
             </p>
            <a href="javascript:void(0);"  onclick="leantime.helperController.startKanbanTour();" class="btn btn-primary"><i class="fas fa-map-signs"></i> Take the Kanban Tour</a><br />
            <a href="javascript:void(0);"  onclick="jquery.nmTop().close()"><i class="fa fa-close"></i> Close</a><br />
            <a href="javascript:void(0);" onclick="leantime.helperController.hideAndKeepHidden('kanban')">Close and don't show this screen again</a>
        </div>
    </div>


</div>
