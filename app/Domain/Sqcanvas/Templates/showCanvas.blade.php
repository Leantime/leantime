@include('canvas::showCanvasTop', ['canvasName' => 'sq'])

    @if (count($tpl->get('allCanvas')) > 0)
        <div id="sortableCanvasKanban" class="sortableTicketList disabled">
            <div class="row-fluid">
                <div class="column" style="width: 100%; min-width: calc(500px);">

                    <div class="row canvas-row">
                        <div class="column" style="width:100%">
                            @include('canvas::element', ['canvasName' => 'sq', 'elementName' => 'sq_qa'])
                        </div>
                    </div>
                    <div class="row canvas-row">
                        <div class="column" style="width:100%">
                            @include('canvas::element', ['canvasName' => 'sq', 'elementName' => 'sq_qb'])
                        </div>
                    </div>
                    <div class="row canvas-row">
                        <div class="column" style="width:100%">
                            @include('canvas::element', ['canvasName' => 'sq', 'elementName' => 'sq_qc'])
                        </div>
                    </div>
                    <div class="row canvas-row">
                        <div class="column" style="width:100%">
                            @include('canvas::element', ['canvasName' => 'sq', 'elementName' => 'sq_qd'])
                        </div>
                    </div>
                    <div class="row canvas-row">
                        <div class="column" style="width:100%">
                            @include('canvas::element', ['canvasName' => 'sq', 'elementName' => 'sq_qe'])
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    @endif

@include('canvas::showCanvasBottom', ['canvasName' => 'sq'])
