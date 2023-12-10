<?php
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$provider = $tpl->get("provider");
$currentStep = $_GET['step'] ?? 'connect';

$totalSteps = count($provider->steps);
$completed = $provider->stepDetails[$currentStep]['position'];
$halfStep = (1 / $totalSteps * 100) / 2;
$percentDone = ($completed / $totalSteps) * 100 - $halfStep;

$i = 0;
?>
<br />
<div class="projectSteps">
    <div class="progressWrapper">
        <div class="progress">
            <div
                id="progressChecklistBar"
                class="progress-bar progress-bar-success tx-transition"
                role="progressbar"
                aria-valuenow="0"
                aria-valuemin="0"
                aria-valuemax="100"
                style="width: <?=$percentDone?>%"
            ><span class="sr-only"></span></div>
        </div>

        <?php foreach ($provider->steps as $step) {
            $i++;
            $stepClass = "";
            if ($currentStep == $step) {
                $stepClass = "current";
            }
            if ($provider->stepDetails[$currentStep]['position'] > $provider->stepDetails[$step]['position']) {
                $stepClass = "complete";
            }
            ?>

        <div class="step <?=$stepClass ?>" style="left: <?=($i / $totalSteps * 100) - $halfStep?>%;">
            <a href="javascript:void(0)" data-toggle="dropdown" class="dropdown-toggle">
                <span class="innerCircle"></span>
                <span class="title">
                    <?php if ($provider->stepDetails[$currentStep]['position'] >  $provider->stepDetails[$step]['position']) {?>
                     <i class="fa fa-check"></i>
                    <?php } ?>
                            <?=$provider->stepDetails[$step]['title'] ?>
                        </span>
            </a>
        </div>
        <?php } ?>
    </div>
</div>
