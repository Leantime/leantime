@include('canvas::showCanvasTop', ['canvasName' => 'obm'])

    @if (count($tpl->get('allCanvas')) > 0)
        <div id="sortableCanvasKanban" class="sortableTicketList disabled">
            <div class="row-fluid">
                <div class="column" style="width: 100%; min-width: calc(5 * 250px + 50px);">

                    <div class="row canvas-row" id="firstRow">

                        <div class="column" style="width: 20%">
                            @include('canvas::element', ['canvasName' => 'obm', 'elementName' => 'obm_kp'])
                        </div>

                        <div class="column" style="width: 20%">
                            <div class="row canvas-row" id="firstRowTop">
                                <div class="column" style="width: 100%; padding-top: 0px">
                                    @include('canvas::element', ['canvasName' => 'obm', 'elementName' => 'obm_ka'])
                                </div>
                            </div>
                            <div class="row canvas-row" id="firstRowBottom">
                                <div class="column" style="width: 100%">
                                    @include('canvas::element', ['canvasName' => 'obm', 'elementName' => 'obm_kr'])
                                </div>
                            </div>
                        </div>

                        <div class="column" style="width: 20%">
                            @include('canvas::element', ['canvasName' => 'obm', 'elementName' => 'obm_vp'])
                        </div>

                        <div class="column" style="width: 20%">
                            <div class="row canvas-row" id="firstRowTop">
                                <div class="column" style="width: 100%; padding-top: 0px">
                                    @include('canvas::element', ['canvasName' => 'obm', 'elementName' => 'obm_cr'])
                                </div>
                            </div>
                            <div class="row canvas-row" id="firstRowBottom">
                                <div class="column" style="width: 100%">
                                    @include('canvas::element', ['canvasName' => 'obm', 'elementName' => 'obm_ch'])
                                </div>
                            </div>
                        </div>

                        <div class="column" style="width: 20%">
                            @include('canvas::element', ['canvasName' => 'obm', 'elementName' => 'obm_cs'])
                        </div>

                    </div>

                    <div class="row canvas-row" id="secondRow">

                        <div class="column" style="width: 50%">
                            @include('canvas::element', ['canvasName' => 'obm', 'elementName' => 'obm_fc'])
                        </div>

                        <div class="column" style="width: 50%">
                            @include('canvas::element', ['canvasName' => 'obm', 'elementName' => 'obm_fr'])
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    @endif

@include('canvas::showCanvasBottom', ['canvasName' => 'obm'])
