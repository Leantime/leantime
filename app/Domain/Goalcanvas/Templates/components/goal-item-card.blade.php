@props([
    'row' => [],
    'elementName' => '',
    'filter' => [],
    'statusLabels' => [],
    'relatesLabels' => [],
    'users' => [],
])

@php
    use Leantime\Domain\Comments\Repositories\Comments;

    $filterStatus = $filter['status'] ?? 'all';
    $filterRelates = $filter['relates'] ?? 'all';
    $comments = app()->make(Comments::class);
    $nbcomments = $comments->countComments(moduleId: $row['id']);
    $metricTypeFront = '';
    $metricTypeBack = '';

    if ($row['metricType'] == 'percent') {
        $metricTypeBack = '%';
    } elseif ($row['metricType'] == 'currency') {
        $metricTypeFront = __('language.currency');
    }
@endphp

@if (
    $row['box'] === $elementName &&
        ($filterStatus == 'all' || $filterStatus == $row['status']) &&
        ($filterRelates == 'all' || $filterRelates == $row['relates']))
    <div class="col-md-4">
        <x-global::content.card>
            <div class="row">
                <div class="col-md-12">
                    <div class="inlineDropDownContainer" style="float:right;">
                        @if ($login::userIsAtLeast($roles::$editor))
                            <x-global::content.context-menu>
                                <li class="nav-header">{{ __('subtitles.edit') }}</li>
                                <x-global::actions.dropdown.item variant="link"
                                    href="#/goalcanvas/editCanvasItem/{{ $row['id'] }}" class="goalCanvasModal"
                                    data="item_{{ $row['id'] }}">
                                    {!! __('links.edit_canvas_item') !!}
                                </x-global::actions.dropdown.item>
                                <x-global::actions.dropdown.item variant="link"
                                    href="#/goalcanvas/delCanvasItem/{{ $row['id'] }}" class="delete goalCanvasModal"
                                    data="item_{{ $row['id'] }}">
                                    {!! __('links.delete_canvas_item') !!}
                                </x-global::actions.dropdown.item>
                            </x-global::content.context-menu>
                        @endif
                    </div>

                    <h4 class="mb-3">
                        <strong>Goal:</strong>
                        <a href="#/goalcanvas/editCanvasItem/{{ $row['id'] }}" class="goalCanvasModal"
                            data-item="item_{{ $row['id'] }}">
                            {{ $row['title'] }}
                        </a>
                    </h4>
                    
                    <strong>Metric:</strong> {{ $row['description'] }}
                    <br /><br />

                    <div class="progress" style="margin-bottom:0px;">
                        <div class="progress-bar progress-bar-success" role="progressbar"
                            aria-valuenow="{{ $row['goalProgress'] }}" aria-valuemin="0" aria-valuemax="100"
                            style="width: {{ $row['goalProgress'] }}%">
                            <span
                                class="sr-only">{{ sprintf(__('text.percent_complete'), $row['goalProgress']) }}</span>
                        </div>
                    </div>

                    <div class="row" style="padding-bottom:0px;">
                        <div class="col-md-4">
                            <small>Start:<br />{{ $metricTypeFront . $row['startValue'] . $metricTypeBack }}</small>
                        </div>
                        <div class="col-md-4 center">
                            <small>{{ __('label.current') }}:<br />{{ $metricTypeFront . $row['currentValue'] . $metricTypeBack }}</small>
                        </div>
                        <div class="col-md-4" style="text-align:right">
                            <small>{{ __('label.goal') }}:<br />{{ $metricTypeFront . $row['endValue'] . $metricTypeBack }}</small>
                        </div>
                    </div>

                    <x-global::actions.dropdown :label-text="'status'" contentRole="link" position="bottom" align="start"
                        :selectable="true" class="status">
                        <x-slot:menu>
                            <li class="nav-header border">{{ __('dropdown.choose_milestone') }}</li>
                            <x-global::actions.dropdown.item style="background-color: #b0b0b0"
                                href="javascript:void(0);" data-label="Select status" data-value="status">
                                {!! __('dropdown.choose_status') !!}
                            </x-global::actions.dropdown.item>

                            @foreach ($statusLabels as $key => $data)
                                <li class='dropdown-item'>
                                    <a href="javascript:void(0);" class="label-{{ $data['dropdown'] }}"
                                        data-label='{{ $data['title'] }}' data-value="{{ $row['id'] . '/' . $key }}"
                                        id="ticketStatusChange{{ $row['id'] . $key }}">{!! $data['title'] !!}</a>
                                </li>
                            @endforeach
                        </x-slot:menu>
                    </x-global::actions.dropdown>

                    @if (!empty($relatesLabels))
                        <x-global::forms._archive.dropdownPill>
                            @foreach ($relatesLabels as $key => $data)
                                <x-global::forms._archive.dropdownPillOption value="{{ $key }}"
                                    :selected="$row['relates'] === $key">
                                    {{ $data['title'] }}
                                </x-global::forms._archive.dropdownPillOption>
                            @endforeach
                        </x-global::forms._archive.dropdownPill>
                    @endif

                    <div class="right">
                        <span class="fas fa-comments"></span>
                        <small>{{ $nbcomments }}</small>
                    </div>
                </div>
            </div>
        </x-global::content.card>
    </div>
@endif
