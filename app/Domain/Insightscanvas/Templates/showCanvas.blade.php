<?php

/**
 * Template
 */
defined('RESTRICTED') or exit('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$canvasName = 'insights';
?>

@include('canvas::showCanvasTop', array_merge($__data, ['canvasName' => 'insights']))


<?php if (count($allCanvas) > 0) { ?>
<div id="sortableCanvasKanban" class="sortableTicketList disabled">
    <div class="row-fluid">
        <div class="column" style="width: 100%; min-width: calc(5 * 250px);">

            <div class="row canvas-row" id="firstRow">
                <?php foreach ($canvasTypes as $key => $box) { ?>
                <div class="column" style="width:20%">
                    @include(
                        'canvas::element',
                        array_merge($__data, ['canvasName' => 'insights', 'elementName' => $key]))
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
<div class="clearfix"></div>
<?php } ?>


@include('canvas::showCanvasBottom', array_merge($__data, ['canvasName' => 'insights']))
