@include('canvas::showCanvasTop', ['canvasName' => 'retros'])

    @if (count($tpl->get('allCanvas')) > 0)
        <div id="sortableCanvasKanban" class="sortableTicketList disabled">
            <div class="row-fluid">
                <div class="column" style="width: 100%; min-width: calc(3 * 250px);">

                    <div class="row canvas-row" id="firstRow">
                        <div class="column" style="width: 33%">
                            @include('canvas::element', ['canvasName' => 'retros', 'elementName' => 'well'])
                        </div>
                        <div class="column" style="width: 33%">
                            @include('canvas::element', ['canvasName' => 'retros', 'elementName' => 'notwell'])
                        </div>
                        <div class="column" style="width: 33%">
                            @include('canvas::element', ['canvasName' => 'retros', 'elementName' => 'startdoing'])
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    @endif

@include('canvas::showCanvasBottom', ['canvasName' => 'retros'])
