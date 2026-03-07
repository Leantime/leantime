@include('canvas::showCanvasTop', ['canvasName' => 'minempathy'])

    @if (count($tpl->get('allCanvas')) > 0)
        <div id="sortableCanvasKanban" class="sortableTicketList disabled">
            <div class="row-fluid">
                <div class="column" style="width: 100%; min-width: calc(2 * 250px);">

                    <div class="row canvas-row" id="firstRow">
                        <div class="column" style="width:50%">
                            @include('canvas::element', ['canvasName' => 'minempathy', 'elementName' => 'minempathy_who'])
                        </div>
                        <div class="column" style="width:50%">
                            @include('canvas::element', ['canvasName' => 'minempathy', 'elementName' => 'minempathy_struggles'])
                        </div>
                    </div>

                    <div class="row canvas-row" id="secondRow">
                        <div style="width:25%"></div>
                        <div class="column" style="width:50%">
                            @include('canvas::element', ['canvasName' => 'minempathy', 'elementName' => 'minempathy_where'])
                        </div>
                        <div style="width:25%"></div>
                    </div>

                    <div class="row canvas-row" id="thirdRow">
                        <div class="column" style="width:50%">
                            @include('canvas::element', ['canvasName' => 'minempathy', 'elementName' => 'minempathy_why'])
                        </div>
                        <div class="column" style="width:50%">
                            @include('canvas::element', ['canvasName' => 'minempathy', 'elementName' => 'minempathy_how'])
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    @endif

@include('canvas::showCanvasBottom', ['canvasName' => 'minempathy'])
