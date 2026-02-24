@include('canvas::showCanvasTop', ['canvasName' => 'cp'])

    @if (count($tpl->get('allCanvas')) > 0)
        <div id="sortableCanvasKanban" class="sortableTicketList disabled">
            <div class="row-fluid">
                <div class="column" style="width: 100%; min-width: calc(7 * 250px);">

                    <div class="row canvas-row">
                        <div class="column" style="width: 16%">
                        </div>
                        <div class="column" style="width: 84%">
                            <h4 class="widgettitle title-primary center canvas-title-only">
                                <large><i class="fa fa-user-doctor"></i> {{ $tpl->__('box.header.cp.cj') }}</large>
                            </h4>
                        </div>
                    </div>

                    <div class="row canvas-row" id="firstRow">
                        <div class="column" style="width: 16%">
                            <h4 class="widgettitle title-primary center canvas-element-title-empty">&nbsp;</h4>
                            <div class="contentInner even status_cp canvas-element-center-middle">
                                <strong>{{ $tpl->__('box.label.cp.need') }}</strong></div>
                        </div>
                        <div class="column" style="width: 28%">
                            @include('canvas::element', ['canvasName' => 'cp', 'elementName' => 'cp_cj_rv'])
                        </div>
                        <div class="column" style="width: 28%">
                            @include('canvas::element', ['canvasName' => 'cp', 'elementName' => 'cp_cj_rc'])
                        </div>
                        <div class="column" style="width: 28%">
                            @include('canvas::element', ['canvasName' => 'cp', 'elementName' => 'cp_cj_e'])
                        </div>
                    </div>

                    <div class="row canvas-row">
                        <div class="column" style="width: 16%">&nbsp;</div>
                        <div class="column center" style="width: 28%"><i class="fa fa-arrows-up-down"></i></div>
                        <div class="column center" style="width: 28%"><i class="fa fa-arrows-up-down"></i></div>
                        <div class="column center" style="width: 28%"><i class="fa fa-arrows-up-down"></i></div>
                    </div>

                    <div class="row canvas-row">
                        <div class="column" style="width: 16%">
                        </div>
                        <div class="column" style="width: 84%">
                            <h4 class="widgettitle title-primary center canvas-title-only">
                                <large><i class="fa fa-barcode"></i> {{ $tpl->__('box.header.cp.ovp') }}</large>
                            </h4>
                        </div>
                    </div>

                    <div class="row canvas-row" id="secondRow">
                        <div class="column" style="width: 16%">
                            <h4 class="widgettitle title-primary center canvas-element-title-empty">&nbsp;</h4>
                            <div class="contentInner even status_cp canvas-element-center-middle">
                                <strong>{{ $tpl->__('box.label.cp.unique') }}</strong></div>
                        </div>
                        <div class="column" style="width: 28%">
                            @include('canvas::element', ['canvasName' => 'cp', 'elementName' => 'cp_ou_rv'])
                        </div>
                        <div class="column" style="width: 28%">
                            @include('canvas::element', ['canvasName' => 'cp', 'elementName' => 'cp_ou_rc'])
                        </div>
                        <div class="column" style="width: 28%">
                            @include('canvas::element', ['canvasName' => 'cp', 'elementName' => 'cp_ou_e'])
                        </div>
                    </div>
                    <div class="row canvas-row" id="thirdRow">
                        <div class="column" style="width: 16%">
                            <h4 class="widgettitle title-primary center canvas-element-title-empty">&nbsp;</h4>
                            <div class="contentInner even status_cp canvas-element-center-middle">
                                <strong>{{ $tpl->__('box.label.cp.superior') }}</strong></div>
                        </div>
                        <div class="column" style="width: 28%">
                            @include('canvas::element', ['canvasName' => 'cp', 'elementName' => 'cp_os_rv'])
                        </div>
                        <div class="column" style="width: 28%">
                            @include('canvas::element', ['canvasName' => 'cp', 'elementName' => 'cp_os_rc'])
                        </div>
                        <div class="column" style="width: 28%">
                            @include('canvas::element', ['canvasName' => 'cp', 'elementName' => 'cp_os_e'])
                        </div>
                    </div>
                    <div class="row canvas-row" id="fourthRow">
                        <div class="column" style="width: 16%">
                            <h4 class="widgettitle title-primary center canvas-element-title-empty">&nbsp;</h4>
                            <div class="contentInner even status_cp canvas-element-center-middle">
                              <strong>{{ $tpl->__('box.label.cp.indifferent') }}</strong></div>
                        </div>
                        <div class="column" style="width: 28%">
                            @include('canvas::element', ['canvasName' => 'cp', 'elementName' => 'cp_oi_rv'])
                        </div>
                        <div class="column" style="width: 28%">
                            @include('canvas::element', ['canvasName' => 'cp', 'elementName' => 'cp_oi_rc'])
                        </div>
                        <div class="column" style="width: 28%">
                            @include('canvas::element', ['canvasName' => 'cp', 'elementName' => 'cp_oi_e'])
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    @endif

@include('canvas::showCanvasBottom', ['canvasName' => 'cp'])
