@include('canvas::showCanvasTop', ['canvasName' => 'lean'])

    @if (count($tpl->get('allCanvas')) > 0)
        <div id="sortableCanvasKanban" class="sortableTicketList disabled">
            <div class="row-fluid">
                <div class="column" style="width: 100%; min-width: calc(5 * 250px);">

                    <div class="row canvas-row" id="firstRow">
                        <div class="column" style="width: 20%">
                            @include('canvas::element', ['canvasName' => 'lean', 'elementName' => 'problem'])
                        </div>
                        <div class="column" style="width: 20%">
                            @include('canvas::element', ['canvasName' => 'lean', 'elementName' => 'solution'])
                        </div>
                        <div class="column" style="width: 20%">
                            @include('canvas::element', ['canvasName' => 'lean', 'elementName' => 'uniquevalue'])
                        </div>
                        <div class="column" style="width: 20%">
                            @include('canvas::element', ['canvasName' => 'lean', 'elementName' => 'unfairadvantage'])
                        </div>
                        <div class="column" style="width: 20%">
                            @include('canvas::element', ['canvasName' => 'lean', 'elementName' => 'customersegment'])
                        </div>
                    </div>

                    <div class="row canvas-row" id="secondRow">
                        <div class="column" style="width: 20%">
                            @include('canvas::element', ['canvasName' => 'lean', 'elementName' => 'alternatives'])
                        </div>
                        <div class="column" style="width: 20%">
                            @include('canvas::element', ['canvasName' => 'lean', 'elementName' => 'keymetrics'])
                        </div>
                        <div class="column" style="width: 20%">
                            @include('canvas::element', ['canvasName' => 'lean', 'elementName' => 'highlevelconcept'])
                        </div>
                        <div class="column" style="width: 20%">
                            @include('canvas::element', ['canvasName' => 'lean', 'elementName' => 'channels'])
                        </div>
                        <div class="column" style="width: 20%">
                            @include('canvas::element', ['canvasName' => 'lean', 'elementName' => 'earlyadopters'])
                        </div>
                    </div>

                    <div class="row canvas-row" id="thirdRow">
                        <div class="column" style="width: 50%">
                            @include('canvas::element', ['canvasName' => 'lean', 'elementName' => 'cost'])
                        </div>
                        <div class="column" style="width: 50%">
                            @include('canvas::element', ['canvasName' => 'lean', 'elementName' => 'revenue'])
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    @endif

@include('canvas::showCanvasBottom', ['canvasName' => 'lean'])
