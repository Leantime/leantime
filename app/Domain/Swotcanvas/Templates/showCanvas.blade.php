@include('canvas::showCanvasTop', ['canvasName' => 'swot'])

    @if (count($tpl->get('allCanvas')) > 0)
        <div id="sortableCanvasKanban" class="sortableTicketList disabled">
            <div class="row-fluid">
                <div class="column" style="width: 100%; min-width: calc(2 * 250px);">

                    <div class="row canvas-row" id="titleRow">
                        <div class="column" style="width: 50%">
                            <h4 class="widgettitle title-primary center canvas-title-only">
                                <large><x-global::elements.icon name="thumb_up" /> {{ $tpl->__('box.header.swot.helpful') }}</large>
                            </h4>
                        </div>
                        <div class="column" style="width: 50%">
                            <h4 class="widgettitle title-primary center" style="border-radius: var(--box-radius-small);">
                                <large><x-global::elements.icon name="thumb_down" /> {{ $tpl->__('box.header.swot.harmful') }}</large>
                            </h4>
                        </div>
                    </div>

                    <div class="row canvas-row" id="firstRow">
                        <div class="column" style="width: 50%">
                            @include('canvas::element', ['canvasName' => 'swot', 'elementName' => 'swot_strengths'])
                        </div>
                        <div class="column" style="width: 50%">
                            @include('canvas::element', ['canvasName' => 'swot', 'elementName' => 'swot_weaknesses'])
                        </div>
                    </div>

                    <div class="row canvas-row" id="secondRow">
                        <div class="column" style="width: 50%">
                            @include('canvas::element', ['canvasName' => 'swot', 'elementName' => 'swot_opportunities'])
                        </div>
                        <div class="column" style="width: 50%">
                            @include('canvas::element', ['canvasName' => 'swot', 'elementName' => 'swot_threats'])
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    @endif

@include('canvas::showCanvasBottom', ['canvasName' => 'swot'])
