@include('canvas::showCanvasTop', ['canvasName' => 'value'])

    @if (count($tpl->get('allCanvas')) > 0)
        <div id="sortableCanvasKanban" class="sortableTicketList disabled">
            <div class="row-fluid">
                <div class="column" style="width: 100%; min-width: calc(5 * 250px);">

                    <div class="row canvas-row" id="firstRow">
                        <div class="column" style="width: 25%">
                            @include('canvas::element', ['canvasName' => 'value', 'elementName' => 'customersegment'])
                        </div>
                        <div class="column" style="width: 25%">
                            @include('canvas::element', ['canvasName' => 'value', 'elementName' => 'problem'])
                        </div>
                        <div class="column" style="width: 25%">
                            @include('canvas::element', ['canvasName' => 'value', 'elementName' => 'solution'])
                        </div>
                        <div class="column" style="width: 25%">
                            @include('canvas::element', ['canvasName' => 'value', 'elementName' => 'uniquevalue'])
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    @endif

@include('canvas::showCanvasBottom', ['canvasName' => 'value'])
