@include('canvas::showCanvasTop', ['canvasName' => 'risks'])

    @if (count($tpl->get('allCanvas')) > 0)
        <div id="sortableCanvasKanban" class="sortableTicketList disabled">
            <div class="row-fluid">
                <div class="column" style="width: 100%; min-width: calc(2 * 250px);">

                    <div class="row canvas-row" id="firstRow">
                        <div class="column" style="width: 50%">
                            @include('canvas::element', ['canvasName' => 'risks', 'elementName' => 'risks_imp_low_pro_high'])
                        </div>
                        <div class="column" style="width: 50%">
                            @include('canvas::element', ['canvasName' => 'risks', 'elementName' => 'risks_imp_high_pro_high'])
                        </div>
                    </div>

                    <div class="row canvas-row" id="secondRow">
                        <div class="column" style="width: 50%">
                            @include('canvas::element', ['canvasName' => 'risks', 'elementName' => 'risks_imp_low_pro_low'])
                        </div>
                        <div class="column" style="width: 50%">
                            @include('canvas::element', ['canvasName' => 'risks', 'elementName' => 'risks_imp_high_pro_low'])
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    @endif

@include('canvas::showCanvasBottom', ['canvasName' => 'risks'])
