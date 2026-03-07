@include('canvas::showCanvasTop', ['canvasName' => 'sb'])

    @php
        $stakeholderStatusLabels = $statusLabels;
        $statusLabels = [];
    @endphp
    @if (count($tpl->get('allCanvas')) > 0)
        <div id="sortableCanvasKanban" class="sortableTicketList disabled">
          <div class="row-fluid"><div class="column" style="width: 100%; min-width: calc(4 * 250px);">

              <div class="row canvas-row">
                    <div class="column" style="width:100%">
                        @include('canvas::element', ['canvasName' => 'sb', 'elementName' => 'sb_description', 'statusLabels' => []])
                    </div>
              </div>

            <div class="row canvas-row">
                <div class="column" style="width:100%">
                    @include('canvas::element', ['canvasName' => 'sb', 'elementName' => 'sb_industry', 'statusLabels' => []])
                </div>
            </div>

            <div class="row canvas-row" id="stakeholderRow">
                <div class="column" style="width:25%">
                    @include('canvas::element', ['canvasName' => 'sb', 'elementName' => 'sb_st_design', 'statusLabels' => $stakeholderStatusLabels])
                </div>
                <div class="column" style="width:25%">
                    @include('canvas::element', ['canvasName' => 'sb', 'elementName' => 'sb_st_decision', 'statusLabels' => $stakeholderStatusLabels])
                </div>
                <div class="column" style="width:25%">
                    @include('canvas::element', ['canvasName' => 'sb', 'elementName' => 'sb_st_experts', 'statusLabels' => $stakeholderStatusLabels])
                </div>
                <div class="column" style="width:25%">
                    @include('canvas::element', ['canvasName' => 'sb', 'elementName' => 'sb_st_support', 'statusLabels' => $stakeholderStatusLabels])
                </div>
            </div>

            <div class="row canvas-row" id="financialsRow">
                <div class="column" style="width:50%">
                    @include('canvas::element', ['canvasName' => 'sb', 'elementName' => 'sb_budget', 'statusLabels' => []])
                </div>
                <div class="column" style="width:50%">
                    @include('canvas::element', ['canvasName' => 'sb', 'elementName' => 'sb_time', 'statusLabels' => []])
                </div>
            </div>

            <div class="row canvas-row" id="culturechangeRow">
                <div class="column" style="width:50%">
                    @include('canvas::element', ['canvasName' => 'sb', 'elementName' => 'sb_culture', 'statusLabels' => []])
                </div>
                <div class="column" style="width:50%">
                    @include('canvas::element', ['canvasName' => 'sb', 'elementName' => 'sb_change', 'statusLabels' => []])
                </div>
            </div>

            <div class="row canvas-row">
                <div class="column" style="width:100%">
                    @include('canvas::element', ['canvasName' => 'sb', 'elementName' => 'sb_principles', 'statusLabels' => []])
                </div>
            </div>

            <div class="row canvas-row">
                <div class="column" style="width:100%">
                   <h4 class="widgettitle title-primary center"><x-global::elements.icon name="personal_injury" /> {{ $tpl->__('box.sb.risks') }}</h4>
                   <div class="contentInner even" style="padding-top: 10px;">
                     {!! sprintf($tpl->__('text.sb.risks_analysis'), BASE_URL) !!}
                   </div>
                </div>
            </div>
          </div></div>
        </div>
        <div class="clearfix"></div>
    @endif

@include('canvas::showCanvasBottom', ['canvasName' => 'sb'])
