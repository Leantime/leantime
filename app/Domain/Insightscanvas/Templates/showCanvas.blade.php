@extends($layout)
@section('content')

@php $canvasName = 'insights'; @endphp

@include('canvas::showCanvasTop', ['canvasName' => 'insights'])

    @if(count($allCanvas) > 0)
        <div id="sortableCanvasKanban" class="sortableTicketList disabled">
          <div class="row-fluid"><div class="column" style="width: 100%; min-width: calc(5 * 250px);">

            <div class="row canvas-row" id="firstRow">
                @foreach($canvasTypes as $key => $box)
                    <div class="column" style="width:20%">
                        @include('canvas::element', ['canvasName' => 'insights', 'elementName' => $key])
                    </div>
                @endforeach
            </div>
          </div></div>
        </div>
        <div class="clearfix"></div>
    @endif

@include('canvas::showCanvasBottom', ['canvasName' => 'insights'])

@endsection
