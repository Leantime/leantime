@extends($layout)

@section('content')
    <?php
    
    /**
     * Template
     */
    defined('RESTRICTED') or exit('Restricted access');
    foreach ($__data as $var => $val) {
        $$var = $val; // necessary for blade refactor
    }
    $canvasName = 'lean';
    ?>

    <?php echo $tpl->viewFactory->make($tpl->getTemplatePath('canvas', 'showCanvasTop'), array_merge($__data, ['canvasName' => 'lean']))->render(); ?>

    <?php if (count($allCanvas) > 0) { ?>
    <div id="sortableCanvasKanban" class="sortableTicketList disabled">
        <div class="row-fluid">
            <div class="column" style="width: 100%; min-width: calc(5 * 250px);">

                <div class="row canvas-row" id="firstRow">
                    <div class="column" style="width: 20%">
                        <?php echo $tpl->viewFactory->make($tpl->getTemplatePath('canvas', 'element'), array_merge($__data, ['canvasName' => 'lean', 'elementName' => 'problem']))->render(); ?>
                    </div>
                    <div class="column" style="width: 20%">
                        <?php echo $tpl->viewFactory->make($tpl->getTemplatePath('canvas', 'element'), array_merge($__data, ['canvasName' => 'lean', 'elementName' => 'solution']))->render(); ?>
                    </div>
                    <div class="column" style="width: 20%">
                        <?php echo $tpl->viewFactory->make($tpl->getTemplatePath('canvas', 'element'), array_merge($__data, ['canvasName' => 'lean', 'elementName' => 'uniquevalue']))->render(); ?>
                    </div>
                    <div class="column" style="width: 20%">
                        <?php echo $tpl->viewFactory->make($tpl->getTemplatePath('canvas', 'element'), array_merge($__data, ['canvasName' => 'lean', 'elementName' => 'unfairadvantage']))->render(); ?>
                    </div>
                    <div class="column" style="width: 20%">
                        <?php echo $tpl->viewFactory->make($tpl->getTemplatePath('canvas', 'element'), array_merge($__data, ['canvasName' => 'lean', 'elementName' => 'customersegment']))->render(); ?>
                    </div>
                </div>

                <div class="row canvas-row" id="firstRow">
                    <div class="column" style="width: 20%">
                        <?php echo $tpl->viewFactory->make($tpl->getTemplatePath('canvas', 'element'), array_merge($__data, ['canvasName' => 'lean', 'elementName' => 'alternatives']))->render(); ?>
                    </div>
                    <div class="column" style="width: 20%">
                        <?php echo $tpl->viewFactory->make($tpl->getTemplatePath('canvas', 'element'), array_merge($__data, ['canvasName' => 'lean', 'elementName' => 'keymetrics']))->render(); ?>
                    </div>
                    <div class="column" style="width: 20%">
                        <?php echo $tpl->viewFactory->make($tpl->getTemplatePath('canvas', 'element'), array_merge($__data, ['canvasName' => 'lean', 'elementName' => 'highlevelconcept']))->render(); ?>
                    </div>
                    <div class="column" style="width: 20%">
                        <?php echo $tpl->viewFactory->make($tpl->getTemplatePath('canvas', 'element'), array_merge($__data, ['canvasName' => 'lean', 'elementName' => 'channels']))->render(); ?>
                    </div>
                    <div class="column" style="width: 20%">
                        <?php echo $tpl->viewFactory->make($tpl->getTemplatePath('canvas', 'element'), array_merge($__data, ['canvasName' => 'lean', 'elementName' => 'earlyadopters']))->render(); ?>
                    </div>
                </div>

                <div class="row canvas-row" id="thirdRow">
                    <div class="column" style="width: 50%">
                        <?php echo $tpl->viewFactory->make($tpl->getTemplatePath('canvas', 'element'), array_merge($__data, ['canvasName' => 'lean', 'elementName' => 'cost']))->render(); ?>
                    </div>
                    <div class="column" style="width: 50%">
                        <?php echo $tpl->viewFactory->make($tpl->getTemplatePath('canvas', 'element'), array_merge($__data, ['canvasName' => 'lean', 'elementName' => 'revenue']))->render(); ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <div class="clearfix"></div>
    <?php } ?>
    @include('canvas::showCanvasBottom', array_merge($__data, ['canvasName' => 'lean']))
@endsection
