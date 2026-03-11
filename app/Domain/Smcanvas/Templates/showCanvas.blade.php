@include('canvas::showCanvasTop', ['canvasName' => 'sm'])

    @if (count($tpl->get('allCanvas')) > 0)
        <div id="sortableCanvasKanban" class="sortableTicketList disabled">
            <div class="row-fluid">
                <div class="column" style="width: 100%; min-width: calc(500px);">

                    <div class="row canvas-row">
                        <div class="column" style="width:100%">
                            @include('canvas::element', ['canvasName' => 'sm', 'elementName' => 'sm_qa'])
                        </div>
                    </div>
                    <div class="row canvas-row">
                        <div class="column" style="width:100%">
                            @include('canvas::element', ['canvasName' => 'sm', 'elementName' => 'sm_qb'])
                        </div>
                    </div>
                    <div class="row canvas-row">
                        <div class="column" style="width:100%">
                            @include('canvas::element', ['canvasName' => 'sm', 'elementName' => 'sm_qc'])
                        </div>
                    </div>
                    <div class="row canvas-row">
                        <div class="column" style="width:100%">
                            @include('canvas::element', ['canvasName' => 'sm', 'elementName' => 'sm_qd'])
                        </div>
                    </div>
                    <div class="row canvas-row">
                        <div class="column" style="width:100%">
                            @include('canvas::element', ['canvasName' => 'sm', 'elementName' => 'sm_qe'])
                        </div>
                    </div>
                    <div class="row canvas-row">
                        <div class="column" style="width:100%">
                            @include('canvas::element', ['canvasName' => 'sm', 'elementName' => 'sm_qf'])
                        </div>
                    </div>
                    <div class="row canvas-row">
                        <div class="column" style="width:100%">
                            @include('canvas::element', ['canvasName' => 'sm', 'elementName' => 'sm_qg'])
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    @endif

@include('canvas::showCanvasBottom', ['canvasName' => 'sm'])
