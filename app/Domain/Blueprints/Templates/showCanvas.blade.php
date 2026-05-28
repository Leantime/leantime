@extends($layout)
@section('content')

@include('blueprints::showCanvasTop', ['canvasSlug' => $canvasSlug])

@if(count($allCanvas) > 0)
    <div id="sortableCanvasKanban" class="sortableTicketList disabled">
        <div class="row-fluid">
            <div class="column" style="width: 100%; min-width: calc({{ $template->minColumns }} * 250px);">
                @foreach($template->layout as $row)
                    @if($row['type'] === 'header')
                        @include('blueprints::partials.sectionHeader', ['row' => $row])
                    @elseif($row['type'] === 'separator')
                        @include('blueprints::partials.separator', ['row' => $row])
                    @elseif($row['type'] === 'static')
                        @include('blueprints::partials.staticContent', ['row' => $row])
                    @elseif($row['type'] === 'boxes')
                        <div class="row canvas-row" @if(isset($row['id'])) id="{{ $row['id'] }}" @endif>
                            @foreach($row['columns'] as $col)
                                <div class="column" style="width: {{ $col['width'] }}%">
                                    @if(isset($col['box']))
                                        @php
                                            // Handle per-box statusLabels overrides
                                            $boxStatusLabels = $statusLabels;
                                            if (array_key_exists('statusLabels', $col)) {
                                                if ($col['statusLabels'] === 'inherit') {
                                                    $boxStatusLabels = $statusLabels;
                                                } elseif (is_array($col['statusLabels'])) {
                                                    $boxStatusLabels = $col['statusLabels'];
                                                } else {
                                                    $boxStatusLabels = [];
                                                }
                                            }
                                        @endphp
                                        @include('blueprints::element', [
                                            'canvasSlug' => $canvasSlug,
                                            'elementName' => $col['box'],
                                            'statusLabels' => $boxStatusLabels,
                                            'relatesLabels' => $relatesLabels,
                                        ])
                                    @elseif(isset($col['label']))
                                        @include('blueprints::partials.rowLabel', ['label' => $col['label']])
                                    @elseif(isset($col['nested']) && $col['nested'] === true && isset($col['rows']))
                                        {{-- Nested sub-grid (used by DBM and OBM canvases) --}}
                                        @foreach($col['rows'] as $subRow)
                                            <div class="row canvas-row" @if(isset($subRow['id'])) id="{{ $subRow['id'] }}" @endif>
                                                @foreach($subRow['columns'] as $subCol)
                                                    <div class="column" style="width: {{ $subCol['width'] }}%">
                                                        @if(isset($subCol['box']))
                                                            @include('blueprints::element', [
                                                                'canvasSlug' => $canvasSlug,
                                                                'elementName' => $subCol['box'],
                                                                'statusLabels' => $statusLabels,
                                                                'relatesLabels' => $relatesLabels,
                                                            ])
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
@endif

@include('blueprints::showCanvasBottom', ['canvasSlug' => $canvasSlug])

@endsection
