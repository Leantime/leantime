@include('canvas::showCanvasTop', ['canvasName' => 'em'])

    @if (count($tpl->get('allCanvas')) > 0)
        <div id="sortableCanvasKanban" class="sortableTicketList disabled">
            <div class="row-fluid"><div class="column" style="width: 100%; min-width: calc(4 * 250px);">

                <div class="row canvas-row">
                    <div class="column" style="width:100%">
                        <x-globals::elements.section-title variant="primary" class="center canvas-title-only">
                            <x-globals::elements.icon name="adjust" /> {{ $tpl->__('box.em.header.goal') }}
                        </x-globals::elements.section-title>
                    </div>
                </div>

                <div class="row canvas-row" id="firstRow">
                    <div class="column" style="width: 50%">
                        @include('canvas::element', ['canvasName' => 'em', 'elementName' => 'em_who'])
                    </div>
                    <div class="column" style="width: 50%">
                        @include('canvas::element', ['canvasName' => 'em', 'elementName' => 'em_what'])
                    </div>
                </div>

                <div class="row canvas-row">
                    <div class="column" style="width: 100%">
                        <x-globals::elements.section-title variant="primary" class="center canvas-title-only">
                            <x-globals::elements.icon name="favorite" /> {{ $tpl->__('box.em.header.empathy') }}
                        </x-globals::elements.section-title>
                    </div>
                </div>

                <div class="row canvas-row" id="secondRow">
                    <div class="column" style="width: 25%">
                        @include('canvas::element', ['canvasName' => 'em', 'elementName' => 'em_see'])
                    </div>
                    <div class="column" style="width: 25%">
                        @include('canvas::element', ['canvasName' => 'em', 'elementName' => 'em_say'])
                    </div>
                    <div class="column" style="width: 25%">
                        @include('canvas::element', ['canvasName' => 'em', 'elementName' => 'em_do'])
                    </div>
                    <div class="column" style="width: 25%">
                        @include('canvas::element', ['canvasName' => 'em', 'elementName' => 'em_hear'])
                    </div>
                </div>

                <div class="row canvas-row">
                    <div class="column" style="width: 100%">
                        <x-globals::elements.section-title variant="primary" class="center canvas-title-only">
                            <x-globals::elements.icon name="counter_7" /> {{ $tpl->__('box.em.header.think_feel') }}
                        </x-globals::elements.section-title>
                    </div>
                </div>

                <div class="row canvas-row" id="thirdRow">
                    <div class="column" style="width: 50%">
                        @include('canvas::element', ['canvasName' => 'em', 'elementName' => 'em_pains'])
                    </div>
                    <div class="column" style="width: 50%">
                        @include('canvas::element', ['canvasName' => 'em', 'elementName' => 'em_gains'])
                    </div>
                </div>

                <div class="row canvas-row" id="fourthRow">
                    <div class="column" style="width: 100%">
                        @include('canvas::element', ['canvasName' => 'em', 'elementName' => 'em_motives'])
                    </div>
                </div>
            </div></div>
        </div>
        <div class="clearfix"></div>
    @endif

@include('canvas::showCanvasBottom', ['canvasName' => 'em'])
