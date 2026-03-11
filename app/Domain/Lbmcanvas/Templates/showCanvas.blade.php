@include('canvas::showCanvasTop', ['canvasName' => 'lbm'])

    @if (count($tpl->get('allCanvas')) > 0)
        <div id="sortableCanvasKanban" class="sortableTicketList disabled">
            <div class="row-fluid">
                <div class="column" style="width: 100%; min-width: calc(3 * 250px);">

                    <div class="row canvas-row" id="firstRow">
                        <div class="column" style="width: 33.33%">
                            @include('canvas::element', ['canvasName' => 'lbm', 'elementName' => 'lbm_customers'])
                        </div>
                        <div class="column" style="width: 33.33%">
                            @include('canvas::element', ['canvasName' => 'lbm', 'elementName' => 'lbm_offerings'])
                        </div>
                        <div class="column" style="width: 33.33%">
                            @include('canvas::element', ['canvasName' => 'lbm', 'elementName' => 'lbm_capabilities'])
                        </div>
                    </div>

                    <div class="row canvas-row" id="secondRow">
                        <div class="column" style="width: 100%">
                            @include('canvas::element', ['canvasName' => 'lbm', 'elementName' => 'lbm_financials'])
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    @endif

@include('canvas::showCanvasBottom', ['canvasName' => 'lbm'])
