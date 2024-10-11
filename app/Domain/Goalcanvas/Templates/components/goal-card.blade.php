
@props([ 
    'canvasId' => '',
    'canvasTitle' => '',
    'goalItems' => [],
    'statusLabels' => [],
    'relatesLabels' => [],
    'users' => [],
    'id' => '',
    ])


@if(!empty($id))
    <div hx-get="{{BASE_URL}}/hx/goalcanvas/goalcanvasCard/get?id={{$id}}"
        hx-trigger="load"
        hx-swap="innerHtml"
    >

        loading...
        {{-- <x-global::content.card>
            <x-global::elements.loader />
        </x-global::content.card> --}}
    </div>
@else 
    <x-global::content.card>
        <div class="row">
            <div class="col-md-12">
                <a href="#/goalcanvas/editCanvasItem?type=goal&canvasId={{ $canvasId }}"
                    class="btn btn-primary pull-right">
                    <i class="fa fa-plus"></i> Create New Goal
                </a>
                <h5 class='subtitle'>
                    <a href='{{ BASE_URL }}/goalcanvas/showCanvas/{{ $canvasId }}'>
                        {{ $canvasTitle }}
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
                                <x-goalcanvas::goal-item-card 
                                    :row="$goal" elementName="goal"
                                    :filter="$filter ?? []" 
                                    :statusLabels="$statusLabels" 
                                    :relatesLabels="$relatesLabels" 
                                    :users="$users"
                                />
                            @endforeach
                        </div>
                        <br />
                    </div>
                </div>
            </div>
        </div>
    </x-global::content.card>
@endif