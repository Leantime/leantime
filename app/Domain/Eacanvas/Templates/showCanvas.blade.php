@include('canvas::showCanvasTop', ['canvasName' => 'ea'])

    @if (count($tpl->get('allCanvas')) > 0)
        <div id="sortableCanvasKanban" class="sortableTicketList disabled">
            <div class="row-fluid">
                <div class="column" style="width: 100%; min-width: calc(4 * 250px);">

                    <div class="row canvas-row" id="firstRow">
                        <div class="column" style="width: 33.33%">
                            @include('canvas::element', ['canvasName' => 'ea', 'elementName' => 'ea_political'])
                        </div>
                        <div class="column" style="width: 33.33%">
                            @include('canvas::element', ['canvasName' => 'ea', 'elementName' => 'ea_economic'])
                        </div>
                        <div class="column" style="width: 33.33%">
                            @include('canvas::element', ['canvasName' => 'ea', 'elementName' => 'ea_societal'])
                        </div>
                    </div>

                    <div class="row canvas-row" id="secondRow">
                        <div class="column" style="width: 33.33%">
                            @include('canvas::element', ['canvasName' => 'ea', 'elementName' => 'ea_technological'])
                        </div>
                        <div class="column" style="width: 33.33%">
                            @include('canvas::element', ['canvasName' => 'ea', 'elementName' => 'ea_legal'])
                        </div>
                        <div class="column" style="width: 33.33%">
                            @include('canvas::element', ['canvasName' => 'ea', 'elementName' => 'ea_ecological'])
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    @endif

@include('canvas::showCanvasBottom', ['canvasName' => 'ea'])
