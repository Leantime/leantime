@props([
    'canvasId' => '',
    'canvasTitle' => '',
    'goalItems' => [],
    'statusLabels' => [],
    'relatesLabels' => [],
    'users' => [],
    'userRoles' => [],
])

<x-global::content.card>
    <div class="row">
        <div class="col-md-12">
            <a href="#/goalcanvas/editCanvasItem?type=goal&canvasId={{ $canvasId }}" class="btn btn-primary pull-right">
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
        <div id="sortableCanvasKanban-{{ $canvasId }}" class="sortableTicketList disabled col-md-12" style="padding-top:15px;">
            <div class="row">
                <div class="col-md-12">
                    <div class="row">
                        @if (count($goalItems) == 0)
                            <div class='col-md-12'>No goals on this board yet. Open the <a href='{{ BASE_URL }}/goalcanvas/showCanvas/{{ $canvasId }}'>board</a> to start adding goals</div>
                        @endif

                        @foreach ($goalItems as $goal)
                            <div class="col-md-4">
                                <div class="ticketBox" id="item_{{ $goal['id'] }}">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="inlineDropDownContainer" style="float:right;">
                                                @if (in_array($userRoles['currentUserRole'], $userRoles['editor']))
                                                    <x-global::content.context-menu>
                                                        <li class="nav-header">{{ __('subtitles.edit') }}</li>
                                                        <x-global::actions.dropdown.item variant="link"
                                                            href="#/goalcanvas/editCanvasItem/{{ $goal['id'] }}" class="goalCanvasModal"
                                                            data="item_{{ $goal['id'] }}">
                                                            {!! __('links.edit_canvas_item') !!}
                                                        </x-global::actions.dropdown.item>
                                                        <x-global::actions.dropdown.item variant="link"
                                                            href="#/goalcanvas/delCanvasItem/{{ $goal['id'] }}" class="delete goalCanvasModal"
                                                            data="item_{{ $goal['id'] }}">
                                                            {!! __('links.delete_canvas_item') !!}
                                                        </x-global::actions.dropdown.item>
                                                    </x-global::content.context-menu>
                                                @endif
                                            </div>

                                            <h4>
                                                <strong>Goal:</strong>
                                                <a href="#/goalcanvas/editCanvasItem/{{ $goal['id'] }}" class="goalCanvasModal"
                                                    data-item="item_{{ $goal['id'] }}">
                                                    {{ $goal['title'] }}
                                                </a>
                                            </h4>
                                            <br />
                                            <strong>Metric:</strong> {{ $goal['description'] }}
                                            <br /><br />

                                            <div class="progress" style="margin-bottom:0px;">
                                                <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="{{ $goal['goalProgress'] }}"
                                                    aria-valuemin="0" aria-valuemax="100" style="width: {{ $goal['goalProgress'] }}%">
                                                    <span class="sr-only">{{ sprintf(__('text.percent_complete'), $goal['goalProgress']) }}</span>
                                                </div>
                                            </div>

                                            <div class="row" style="padding-bottom:0px;">
                                                <div class="col-md-4">
                                                    <small>Start:<br />{{ $goal['metricTypeFront'] . $goal['startValue'] . $goal['metricTypeBack'] }}</small>
                                                </div>
                                                <div class="col-md-4 center">
                                                    <small>{{ __('label.current') }}:<br />{{ $goal['metricTypeFront'] . $goal['currentValue'] . $goal['metricTypeBack'] }}</small>
                                                </div>
                                                <div class="col-md-4" style="text-align:right">
                                                    <small>{{ __('label.goal') }}:<br />{{ $goal['metricTypeFront'] . $goal['endValue'] . $goal['metricTypeBack'] }}</small>
                                                </div>
                                            </div>

                                            @if (!empty($statusLabels))
                                                <x-global::forms.dropdownPill>
                                                    @foreach ($statusLabels as $key => $data)
                                                        <x-global::forms.dropdownPillOption value="{{ $key }}" :selected="$goal['status'] === $key">
                                                            {{ $data['title'] }}
                                                        </x-global::forms.dropdownPillOption>
                                                    @endforeach
                                                </x-global::forms.dropdownPill>
                                            @endif

                                            <div class="right" style="margin-right:10px;">
                                                <span class="fas fa-comments"></span>
                                                <small>{{ $goal['nbcomments'] }}</small>
                                            </div>
                                        </div>
                                    </div>
                                    @if ($goal['milestoneHeadline'] != '')
                                        <br />
                                        <div hx-trigger="load" hx-indicator=".htmx-indicator"
                                            hx-get="{{ BASE_URL }}/hx/tickets/milestones/showCard?milestoneId={{ $goal['milestoneId'] }}">
                                            <div class="htmx-indicator">{{ __('label.loading_milestone') }}</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <br />
                </div>
            </div>
        </div>
    </div>
</x-global::content.card>
