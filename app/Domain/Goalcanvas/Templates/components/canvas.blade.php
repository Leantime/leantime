@props([
    'canvasId' => '',
    'canvasTitle' => '',
    'goalItems' => [],
    'statusLabels' => [],
    'relatesLabels' => [],
    'users' => [],
    'id' => '',
])

@if (empty($id) == false)
    <div hx-get="{{ BASE_URL }}/hx/goalcanvas/canvas/get?id={{ $id }}" hx-trigger="load"
        hx-swap="innerHtml">

        loading...

    </div>
@else
    <div class="row">
        <div class="col-md-12">
            <x-global::forms.button scale="sm" class="pull-right" tag="a"
                href="#/goalcanvas/editCanvasItem?type=goal&canvasId={{ $canvasId }}">
                <i class="fa fa-plus"></i> Create New Goal
            </x-global::forms.button>
            <h5 class="text-lg font-extralight">
                <a class="text-primary" href='{{ BASE_URL }}/goalcanvas/showCanvas/{{ $canvasId }}'>
                    {{ $canvas->title }}
                </a>
            </h5>
        </div>
    </div>

    <div class="row" style="border-bottom:1px solid var(--main-border-color); margin-bottom:20px">
        <div id="sortableCanvasKanban-{{ $canvasId }}" class="sortableTicketList disabled col-md-12"
            style="padding-top:15px;">
            <div class="row">
                <div class="col-md-12">
                    <div class="row">
                        @if (count($goalItems) == 0)
                            <div class='col-md-12'>No goals on this board yet. Open the <a
                                    href='{{ BASE_URL }}/goalcanvas/showCanvas/{{ $canvasId }}'>board</a> to
                                start adding goals</div>
                        @endif

                        @foreach ($goalItems as $goal)
                            <x-goalcanvas::goal-item-card :row="$goal" elementName="goal" :filter="$filter ?? []"
                                :statusLabels="$statusLabels" :relatesLabels="$relatesLabels" :users="$users" />
                        @endforeach
                    </div>
                    <br />
                </div>
            </div>
        </div>
    </div>
@endif
