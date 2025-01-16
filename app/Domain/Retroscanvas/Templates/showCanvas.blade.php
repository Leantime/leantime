@extends($layout)
@section('content')
    <?php
    
    /**
     * Template
     */
    
    defined('RESTRICTED') or die('Restricted access');
    
    foreach ($__data as $var => $val) {
        $$var = $val; // necessary for blade refactor
    }
    
    $canvasName = 'retros';
    ?>

    @include('canvas::showCanvasTop', array_merge($__data, ['canvasName' => 'retros']))


    <?php if (count($allCanvas) > 0) { ?>
    <div id="sortableCanvasKanban" class="sortableTicketList disabled">
        <div class="row-fluid">
            <div class="column" style="width: 100%; min-width: calc(3 * 250px);">

                <div class="row canvas-row" id="firstRow">
                    <div class="column" style="width: 33%">
                        @include(
                            'canvas::element',
                            array_merge($__data, ['canvasName' => 'retros', 'elementName' => 'well']))
                    </div>
                    <div class="column" style="width: 33%">
                        @include(
                            'canvas::element',
                            array_merge($__data, ['canvasName' => 'retros', 'elementName' => 'notwell']))
                    </div>
                    <div class="column" style="width: 33%">
                        @include(
                            'canvas::element',
                            array_merge($__data, ['canvasName' => 'retros', 'elementName' => 'startdoing']))
                    </div>

                </div>

            </div>
        </div>
    </div>
    <div class="clearfix"></div>
    <?php } ?>

    @include('canvas::showCanvasBottom', array_merge($__data, ['canvasName' => 'retros']))
@endsection
