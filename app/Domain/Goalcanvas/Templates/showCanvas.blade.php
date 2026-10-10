@extends($layout)
@section('content')


    @php
        $elementName = 'goal';

    @endphp

    @php

        $canvasTitle = '';

        //get canvas title
        foreach ($allCanvas as $canvasRow) {
            if ($canvasRow['id'] == $currentCanvas) {
                $canvasTitle = $canvasRow['title'];
                break;
            }
        }

    @endphp
    <style>
        .canvas-row {
            margin-left: 0px;
            margin-right: 0px;
        }

        .canvas-title-only {
            border-radius: var(--box-radius-small);
        }

        h4.canvas-element-title-empty {
            background: white !important;
            border-color: white !important;
        }

        div.canvas-element-center-middle {
            text-align: center;
        }
    </style>

    <div class="pageheader">
        <div class="pageicon"><span class="fas {{ $canvasIcon }}"></span></div>
        <div class="pagetitle">
            @if (count($allCanvas) > 0)
                <x-global::subjectSwitcher
                    :parent="__('headline.goal.board')"
                    :current="$canvasTitle">
                    @if ($login::userIsAtLeast($roles::$editor))
                        <li><a href="#/goalcanvas/bigRock">{!! __('links.icon.create_new_bigrock') !!}</a></li>
                    @endif
                    <li class="border"></li>
                    @foreach ($allCanvas as $canvasRow)
                        <li><a
                                href='{{ BASE_URL }}/goalcanvas/showCanvas/{{ $canvasRow['id'] }}'>{{ $canvasRow['title'] }}</a>
                        </li>
                    @endforeach
                </x-global::subjectSwitcher>
            @else
                <h1>{{ __('headline.goal.board') }}</h1>
            @endif
        </div>
        @if (count($allCanvas) > 0)
            <div class="pageheader-right">
                <x-global::actions.dropdown variant="header-menu">
                    @if ($login::userIsAtLeast($roles::$editor))
                        <li><a href="#/goalcanvas/bigRock/{{ $currentCanvas }}">{!! __('links.icon.edit') !!}</a></li>
                        <li><a href="javascript:void(0)" class="cloneCanvasLink ">{!! __('links.icon.clone') !!}</a></li>
                        <li><a href="javascript:void(0)" class="mergeCanvasLink ">{!! __('links.icon.merge') !!}</a></li>
                        <li><a href="javascript:void(0)" class="importCanvasLink ">{!! __('links.icon.import') !!}</a>
                        </li>
                    @endif
                    <li><a
                            href="{{ BASE_URL }}/goalcanvas/export/{{ $currentCanvas }}">{!! __('links.icon.export') !!}</a>
                    </li>
                    <li><a href="javascript:window.print();">{!! __('links.icon.print') !!}</a></li>
                    @if ($login::userIsAtLeast($roles::$editor))
                        <li><a href="#/goalcanvas/delCanvas/{{ $currentCanvas }}"
                                class="delete">{!!__('links.icon.delete') !!}</a></li>
                    @endif

                </x-global::actions.dropdown>
            </div>
        @endif
    </div>
    <!--pageheader-->

    <div class="maincontent">
        <div class="maincontentinner">

            <?php echo $tpl->displayNotification(); ?>

            <div class="row">
                <div class="col-md-3">
                    @if ($login::userIsAtLeast($roles::$editor) && count($canvasTypes) == 1 && count($allCanvas) > 0)
                        <x-global::forms.button tag="a" link="#/goalcanvas/editCanvasItem?type={{ $elementName }}" contentRole="primary"
                            id="{{ $elementName }}">{!! __('links.add_new_canvas_itemgoal') !!}</x-global::forms.button>
                    @endif
                </div>

                <div class="col-md-6 center">
                </div>

                <div class="col-md-3">
                    <div class="pull-right">
                        @if (count($allCanvas) > 0 && !empty($statusLabels))
                            @php
                                $filterStatus = $filter['status'] ?? 'all';
                                $filterRelates = $filter['relates'] ?? 'all';
                            @endphp
                            <x-global::actions.dropdown variant="filter">
                                <x-slot:trigger>
                                    @if (($filterStatus ?? '') == 'all')
                                        <i class="fas fa-filter"></i>
                                            {!! __('status.all') !!} {!! __('links.view') !!}
                                    @else
                                        <i
                                                class="fas fa-fw {{ __($statusLabels[$filterStatus]['icon']) }}"></i>
                                            {{ $statusLabels[$filterStatus]['title'] }} {{ __('links.view') }}
                                    @endif
                                </x-slot:trigger>
                                <li><a href="{{ BASE_URL }}/goalcanvas/showCanvas?filter_status=all" @if ($filterStatus == 'all')
                                                class="active"
                                @endif><i class="fas fa-globe"></i> {!! __('status.all') !!}</a></li>
                                @foreach ($statusLabels as $key => $data)
                                    <li><a href="{{ BASE_URL }}/goalcanvas/showCanvas?filter_status={{ $key }}"
                                            @if ($filterStatus == $key)
                                            class="active"
                                @endif><i class="fas fa-fw {{ $data['icon'] }}"></i>
                                {!! $data['title'] !!}</a></li>
                                @endforeach

                            </x-global::actions.dropdown>
                        @endif

                        @if (count($allCanvas) > 0 && !empty($relatesLabels))
                            @php
                                $filterStatus = $filter['status'] ?? 'all';
                                $filterRelates = $filter['relates'] ?? 'all';
                            @endphp
                            <x-global::actions.dropdown variant="filter">
                                <x-slot:trigger>
                                    @if ($filterRelates == 'all')
                                        <i
                                                class="fas fa-fw fa-globe"></i> {{ __('relates.all') }}
                                            {{ __('links.view') }}
                                    @else
                                        <i
                                                class="fas fa-fw {{ __($relatesLabels[$filterRelates]['icon']) }}"></i>
                                            {{ $relatesLabels[$filterRelates]['title'] }} {{ __('links.view') }}
                                    @endif
                                </x-slot:trigger>
                                <li><a href="{{ BASE_URL }}/goalcanvas/showCanvas?filter_relates=all" @if ($filterRelates == 'all')
                                                class="active"
                                @endif><i class="fas fa-globe"></i> {{ __('relates.all') }}</a></li>
                                @foreach ($relatesLabels as $key => $data)
                                    <li><a href="{{ BASE_URL }}/goalcanvas/showCanvas?filter_relates={{ $key }}"
                                            @if ($filterRelates == $key)
                                            class="active"
                                @endif><i class="fas fa-fw {{ $data['icon'] }}"></i>
                                {{ $data['title'] }}</a></li>
                                @endforeach

                            </x-global::actions.dropdown>
                        @endif
                    </div>
                </div>
            </div>

            <div class="clearfix"></div>


            @if (count($allCanvas) > 0)
                <div id="sortableCanvasKanban" class="sortableTicketList disabled" style="padding-top:15px;">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row">
                                @foreach ($canvasItems as $row)
                                    @php
                                        $filterStatus = $filter['status'] ?? 'all';
                                        $filterRelates = $filter['relates'] ?? 'all';
                                    @endphp

                                    @if (
                                        $row['box'] === $elementName &&
                                            ($filterStatus == 'all' || $filterStatus == $row['status']) &&
                                            ($filterRelates == 'all' || $filterRelates == $row['relates']))
                                        @php
                                            $comments = app()->make(\Leantime\Domain\Comments\Repositories\Comments::class);
                                            $nbcomments = $comments->countComments(moduleId: $row['id']);
                                        @endphp
                                        <div class="col-md-4">
                                            <div class="ticketBox" id="item_{{ $row['id'] }}">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        @if ($login::userIsAtLeast($roles::$editor))
                                                            <x-global::actions.dropdown style="float:right;">
                                                                <li class="nav-header">{{ __('subtitles.edit') }}</li>
                                                                <li><a href="#/goalcanvas/editCanvasItem/{{ $row['id'] }}"
                                                                        data="item_{{ $row['id'] }}">
                                                                        {!!   __('links.edit_canvas_item') !!}</a></li>
                                                                <li><a href="#/goalcanvas/delCanvasItem/{{ $row['id'] }}"
                                                                        data="item_{{ $row['id'] }}">
                                                                    {!!  __('links.delete_canvas_item') !!}</a></li>

                                                            </x-global::actions.dropdown>
                                                        @endif

                                            <h4><strong>Goal:</strong> <a
                                                    href="#/goalcanvas/editCanvasItem/{{ $row['id'] }}"
                                                    data="item_{{ $row['id'] }}">{{ $row['title'] }}</a>
                                            </h4>
                                            <br />
                                            <strong>Metric:</strong> {{ $row['description'] }}
                                            <br /><br />

                                            @php
                                                $percentDone = $row['goalProgress'];
                                                $metricTypeFront = '';
                                                $metricTypeBack = '';
                                                if ($row['metricType'] == 'percent') {
                                                    $metricTypeBack = '%';
                                                } elseif ($row['metricType'] == 'currency') {
                                                    $metricTypeFront = __('language.currency');
                                                }
                                            @endphp

                                            <div class="row">
                                                <div class="col-md-4"></div>
                                                <div class="col-md4 center">
                                                    <small>{{ sprintf(__('text.percent_complete'), $percentDone) }}</small>
                                                </div>
                                                <div class="col-md-4"></div>
                                            </div>
                                            <div class="progress" style="margin-bottom:0px;">
                                                <div class="progress-bar progress-bar-success"
                                                    role="progressbar" aria-valuenow="{{ $percentDone }}"
                                                    aria-valuemin="0" aria-valuemax="100"
                                                    style="width: {{ $percentDone }}%">
                                                    <span
                                                        class="sr-only">{{ sprintf(__('text.percent_complete'), $percentDone) }}</span>
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

                                            <div class="clearfix" style="padding-bottom: 8px;"></div>

                                            @if (!empty($statusLabels))
                                                <x-blueprints::chip-label type="status" adapter="goal" :item-id="$row['id']" :value="$row['status'] ?? null" :labels="$statusLabels" />
                                            @endif

                                            @if (!empty($relatesLabels))
                                                <x-blueprints::chip-label type="relates" adapter="goal" :item-id="$row['id']" :value="$row['relates'] ?? null" :labels="$relatesLabels" />
                                            @endif

                                            <x-global::elements.author-avatar class="dropRight" :user-id="$row['author']" :name="trim(($row['authorFirstname'] ?? '').' '.($row['authorLastname'] ?? ''))" />

                                            <div class="right" style="margin-right:10px;">
                                                <a href="#/goalcanvas/editCanvasComment/{{ $row['id'] }}"
                                                    class="commentCountLink"
                                                    data="item_{{ $row['id'] }}"><span
                                                        class="fas fa-comments"></span></a>
                                                <small>{{ $nbcomments }}</small>
                                            </div>

                                        </div>
                                    </div>

                                    @include('goalcanvas::partials.milestoneChips', ['milestones' => $row['milestones'] ?? []])
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
                <br />
            </div>
        </div>
    </div>

    @if (count($canvasItems) == 0)
        <br /><br />
        <div class='center'>
            <div class='svgContainer'>
                {!! file_get_contents(ROOT . '/dist/images/svg/undraw_design_data_khdb.svg') !!}
                        </div>
                        <h3>{{ __('headlines.goal.analysis') }}</h3>
                        <br />{!! __('text.goal.helper_content') !!}
                    </div>
                @endif

                <div class="clearfix"></div>
            @endif




            <!-- ShowBottomCanvs -->


            @if (count($allCanvas) > 0)
            @else
                <br /><br />
                <div class='center'>
                    <div class='svgContainer'>
                        {!! file_get_contents(ROOT . '/dist/images/svg/undraw_design_data_khdb.svg') !!}
                    </div>

                    <h3>{{ __('headlines.goal.analysis') }}</h3>
                    <br />{{ __('text.goal.helper_content') }}

                    @if ($login::userIsAtLeast($roles::$editor))
                        <br /><br />
                        <x-global::forms.button tag="a" link="javascript:void(0)" class="addCanvasLink" contentRole="primary">
                            {{ __('links.icon.create_new_board') }}
                        </x-global::forms.button>
                    @endif
                </div>
            @endif

            @if (!empty($disclaimer) && count($allCanvas) > 0)
                <small class="align-center">{{ $disclaimer }}</small>
            @endif

            {!! $tpl->viewFactory->make($tpl->getTemplatePath('canvas', 'modals'), $__data)->render() !!}
        </div>
    </div>


    <script type="text/javascript">
        jQuery(document).ready(function() {
            leantime.goalCanvasController.setRowHeights();
            leantime.canvasController.setCanvasName('goal');
            leantime.canvasController.initFilterBar();

            @if ($login::userIsAtLeast($roles::$editor))
                leantime.canvasController.initCanvasLinks();
            @else
                leantime.authController.makeInputReadonly(".maincontentinner");
            @endif

            @if (isset($_GET['showModal']))
                @php
                    if ($_GET['showModal'] == '') {
                        $modalUrl = '&type=' . array_key_first($canvasTypes);
                    } else {
                        $modalUrl = '/' . (int) $_GET['showModal'];
                    }
                @endphp
                leantime.canvasController.openModalManually(
                    "{{ BASE_URL }}/goalcanvas/editCanvasItem{{ $modalUrl }}");
                window.history.pushState({}, document.title,
                    '{{ BASE_URL }}/goalcanvas/showCanvas/');
            @endif
        });
    </script>



@endsection
