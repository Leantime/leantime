@include('canvas::showCanvasTop', ['canvasName' => 'dbm'])

    @if (count($tpl->get('allCanvas')) > 0)
        <div id="sortableCanvasKanban" class="sortableTicketList disabled">
            <div class="row-fluid">
                <div class="column" style="width: 100%; min-width: calc(8 * 250px);">

                    <div class="row canvas-row" id="firstRow">
                        <div class="column" style="width: 20%">
                            @include('canvas::element', ['canvasName' => 'dbm', 'elementName' => 'dbm_cs'])
                        </div>
                        <div class="column" style="width: 20%">
                            @include('canvas::element', ['canvasName' => 'dbm', 'elementName' => 'dbm_cr'])
                        </div>
                        <div class="column" style="width: 20%">
                            @include('canvas::element', ['canvasName' => 'dbm', 'elementName' => 'dbm_ovp'])
                        </div>
                        <div class="column" style="width: 13.33%">
                            @include('canvas::element', ['canvasName' => 'dbm', 'elementName' => 'dbm_kad'])
                        </div>
                        <div class="column" style="width: 13.33%">
                            @include('canvas::element', ['canvasName' => 'dbm', 'elementName' => 'dbm_kac'])
                        </div>
                        <div class="column" style="width: 13.33%">
                            @include('canvas::element', ['canvasName' => 'dbm', 'elementName' => 'dbm_kao'])
                        </div>
                    </div>

                    <div class="row canvas-row" id="secondRow">
                        <div class="column" style="width: 20%">
                            @include('canvas::element', ['canvasName' => 'dbm', 'elementName' => 'dbm_cj'])
                        </div>
                        <div class="column" style="width: 20%">
                            @include('canvas::element', ['canvasName' => 'dbm', 'elementName' => 'dbm_cd'])
                        </div>
                        <div class="column" style="width: 20%">
                            @include('canvas::element', ['canvasName' => 'dbm', 'elementName' => 'dbm_ops'])
                        </div>
                        <div class="column" style="width:40%">
                            <div class="row canvas-row" id="secondRowTop">
                                <div class="column" style="width:50%; padding-top: 0px">
                                    @include('canvas::element', ['canvasName' => 'dbm', 'elementName' => 'dbm_krp'])
                                </div>
                                <div class="column" style="width:50%; padding-top: 0">
                                    @include('canvas::element', ['canvasName' => 'dbm', 'elementName' => 'dbm_krc'])
                                </div>
                            </div>
                            <div class="row canvas-row" id="secondRowBottom">
                                <div class="column" style="width:50%; padding-bottom: 0">
                                    @include('canvas::element', ['canvasName' => 'dbm', 'elementName' => 'dbm_krl'])
                                </div>
                                <div class="column" style="width:50%; padding-bottom: 0">
                                    @include('canvas::element', ['canvasName' => 'dbm', 'elementName' => 'dbm_krs'])
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="row canvas-row" id="thirdRow">
                        <div class="column" style="width: 50%">
                            @include('canvas::element', ['canvasName' => 'dbm', 'elementName' => 'dbm_fr'])
                        </div>
                        <div class="column" style="width: 50%">
                            @include('canvas::element', ['canvasName' => 'dbm', 'elementName' => 'dbm_fc'])
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    @endif

@include('canvas::showCanvasBottom', ['canvasName' => 'dbm'])
