<?php
  $currentSprint = $this->get('sprint');
?>

<div class="center padding-lg">

    <div class="row">
        <div class="col-md-12">
            <span class="bigIcon primaryColor"><span class="fa fa-suitcase"></span></span><br />
            <h3 class="primaryColor">Congratulations, on your project!</h3><br />
            <p>You can now go to <a href="/leancanvas/simpleCanvas/" class="btn btn-primary" ><span class="fas fa-flask"></span> Research</a> to identify your Customer, Problem and Solution Fit. <br /><br />Or, to skip research and go right into planning, go to the <a href="/tickets/roadmap/" class="btn btn-primary"><span class="fas fa-map"></span> Roadmap</a><br /><br /></p>
        </div>
    </div>


    <div class="row">
        <div class="col-md-12 align-center">
            <a href="javascript:void(0);"  onclick="jQuery.nmTop().close()"><i class="fa fa-close"></i> Close</a><br />
            <a href="javascript:void(0);" onclick="leantime.helperController.hideAndKeepHidden('projectSuccess')">Close and don't show this screen again</a>
        </div>
    </div>


</div>
