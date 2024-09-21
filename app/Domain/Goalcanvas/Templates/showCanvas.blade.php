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

        $tpl->assign('canvasTitle', $canvasTitle);

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
            <h5>{{ session('currentProjectClient') . ' // ' . session('currentProjectName') }}</h5>
            @if (count($allCanvas) > 0)
                <span class="dropdown dropdownWrapper headerEditDropdown">
                    <a href="javascript:void(0)" class="dropdown-toggle btn btn-transparent" data-toggle="dropdown"><i
                            class="fa-solid fa-ellipsis-v"></i></a>
                    <ul class="dropdown-menu editCanvasDropdown">
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
                    </ul>
                </span>
            @endif
            <h1>{{ __('headline.goal.board') }} //
                @if (count($allCanvas) > 0)
                    <span class="dropdown dropdownWrapper">
                        <a href="javascript:void(0);" class="dropdown-toggle header-title-dropdown" data-toggle="dropdown">
                            {{ $canvasTitle }}&nbsp;<i class="fa fa-caret-down"></i>
                        </a>

                        <ul class="dropdown-menu canvasSelector">
                            @if ($login::userIsAtLeast($roles::$editor))
                                <li><a href="#/goalcanvas/bigRock">{!! __('links.icon.create_new_bigrock') !!}</a></li>
                            @endif
                            <li class="border"></li>
                            @foreach ($allCanvas as $canvasRow)
                                <li><a
                                        href='{{ BASE_URL }}/goalcanvas/showCanvas/{{ $canvasRow['id'] }}'>{{ $canvasRow['title'] }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </span>
                @endif
            </h1>
        </div>
    </div>
    <!--pageheader-->

    <div class="maincontent">
        <div class="maincontentinner">

            <?php echo $tpl->displayNotification(); ?>

            <div class="row">
                <div class="col-md-3">
                    @if ($login::userIsAtLeast($roles::$editor) && count($canvasTypes) == 1 && count($allCanvas) > 0)
                        <a href="#/goalcanvas/editCanvasItem?type={{ $elementName }}" class="btn btn-primary"
                            id="{{ $elementName }}">{!! __('links.add_new_canvas_itemgoal') !!}</a>
                    @endif
                </div>

                <div class="col-md-6 center">
                </div>

                <div class="col-md-3">
                    <div class="pull-right">
                        <div class="btn-group viewDropDown">
                            @if (count($allCanvas) > 0 && !empty($statusLabels))
                                @php
                                    $filterStatus = $filter['status'] ?? 'all';
                                    $filterRelates = $filter['relates'] ?? 'all';
                                @endphp

                                @if (($filterStatus ?? '') == 'all')
                                    <button class="btn dropdown-toggle" data-toggle="dropdown"><i class="fas fa-filter"></i>
                                        {!! __('status.all') !!} {!! __('links.view') !!}</button>
                                @else
                                    <button class="btn dropdown-toggle" data-toggle="dropdown"><i
                                            class="fas fa-fw {{ __($statusLabels[$filterStatus]['icon']) }}"></i>
                                        {{ $statusLabels[$filterStatus]['title'] }} {{ __('links.view') }}</button>
                                @endif
                                <ul class="dropdown-menu">
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
                            </ul>
                            @endif
                        </div>

                        <div class="btn-group viewDropDown">
                            @if (count($allCanvas) > 0 && !empty($relatesLabels))
                                @php
                                    $filterStatus = $filter['status'] ?? 'all';
                                    $filterRelates = $filter['relates'] ?? 'all';
                                @endphp

                                @if ($filterRelates == 'all')
                                    <button class="btn dropdown-toggle" data-toggle="dropdown"><i
                                            class="fas fa-fw fa-globe"></i> {{ __('relates.all') }}
                                        {{ __('links.view') }}</button>
                                @else
                                    <button class="btn dropdown-toggle" data-toggle="dropdown"><i
                                            class="fas fa-fw {{ __($relatesLabels[$filterRelates]['icon']) }}"></i>
                                        {{ $relatesLabels[$filterRelates]['title'] }} {{ __('links.view') }}</button>
                                @endif
                                <ul class="dropdown-menu">
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
                            </ul>
                            @endif
                        </div>
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
                                                        <div class="inlineDropDownContainer" style="float:right;">
                                                            @if ($login::userIsAtLeast($roles::$editor))
                                                                <a href="javascript:void(0)"
                                                                    class="dropdown-toggle ticketDropDown"
                                                                    data-toggle="dropdown">
                                                                    <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                                                </a>
                                                                <ul class="dropdown-menu">
                                                                    <li class="nav-header">{{ __('subtitles.edit') }}</li>
                                                                    <li><a href="#/goalcanvas/editCanvasItem/{{ $row['id'] }}"
                                                                            data="item_{{ $row['id'] }}">
                                                                            {!!   __('links.edit_canvas_item') !!}</a></li>
                                                                    <li><a href="#/goalcanvas/delCanvasItem/{{ $row['id'] }}"
                                                                            data="item_{{ $row['id'] }}">
                                                                        {!!  __('links.delete_canvas_item') !!}</a></li>
                                                    </ul>
                                                @endif
                                            </div>

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
                                                <div
                                                    class="dropdown ticketDropdown statusDropdown colorized show firstDropdown">
                                                    <a class="dropdown-toggle f-left status label-{{ $row['status'] != '' ? $statusLabels[$row['status']]['dropdown'] : '' }}"
                                                        href="javascript:void(0);" role="button"
                                                        id="statusDropdownMenuLink{{ $row['id'] }}"
                                                        data-toggle="dropdown" aria-haspopup="true"
                                                        aria-expanded="false">
                                                        <span
                                                            class="text">{{ $row['status'] != '' ? $statusLabels[$row['status']]['title'] : '' }}</span>
                                                        <i class="fa fa-caret-down" aria-hidden="true"></i>
                                                    </a>
                                                    <ul class="dropdown-menu"
                                                        aria-labelledby="statusDropdownMenuLink{{ $row['id'] }}">
                                                        <li class="nav-header border">
                                                            {{ __('dropdown.choose_status') }}</li>
                                                        @foreach ($statusLabels as $key => $data)
                                                            @if ($data['active'] || true)
                                                                <li class='dropdown-item'>
                                                                    <a href="javascript:void(0);"
                                                                        class="label-{{ $data['dropdown'] }}"
                                                                        data-label='{{ $data['title'] }}'
                                                                        data-value="{{ $row['id'] . '/' . $key }}"
                                                                        id="ticketStatusChange{{ $row['id'] . $key }}">{{ $data['title'] }}</a>
                                                                </li>
                                                            @endif
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif

                                            @if (!empty($relatesLabels))
                                                <div
                                                    class="dropdown ticketDropdown relatesDropdown colorized show firstDropdown">
                                                    <a class="dropdown-toggle f-left relates label-{{ $relatesLabels[$row['relates']]['dropdown'] }}"
                                                        href="javascript:void(0);" role="button"
                                                        id="relatesDropdownMenuLink{{ $row['id'] }}"
                                                        data-toggle="dropdown" aria-haspopup="true"
                                                        aria-expanded="false">
                                                        <span
                                                            class="text">{{ $relatesLabels[$row['relates']]['title'] }}</span>
                                                        <i class="fa fa-caret-down" aria-hidden="true"></i>
                                                    </a>
                                                    <ul class="dropdown-menu"
                                                        aria-labelledby="relatesDropdownMenuLink{{ $row['id'] }}">
                                                        <li class="nav-header border">
                                                            {{ __('dropdown.choose_relates') }}</li>
                                                        @foreach ($relatesLabels as $key => $data)
                                                            @if ($data['active'] || true)
                                                                <li class='dropdown-item'>
                                                                    <a href="javascript:void(0);"
                                                                        class="label-{{ $data['dropdown'] }}"
                                                                        data-label='{{ $data['title'] }}'
                                                                        data-value="{{ $row['id'] . '/' . $key }}"
                                                                        id="ticketRelatesChange{{ $row['id'] . $key }}">{{ $data['title'] }}</a>
                                                                </li>
                                                            @endif
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif

                                            <div
                                                class="dropdown ticketDropdown userDropdown noBg show right lastDropdown dropRight">
                                                <a class="dropdown-toggle f-left" href="javascript:void(0);"
                                                    role="button"
                                                    id="userDropdownMenuLink{{ $row['id'] }}"
                                                    data-toggle="dropdown" aria-haspopup="true"
                                                    aria-expanded="false">
                                                    <span class="text">
                                                        @if ($row['authorFirstname'] != '')
                                                            <span id='userImage{{ $row['id'] }}'>
                                                                <img src='{{ BASE_URL }}/api/users?profileImage={{ $row['author'] }}'
                                                                    width='25'
                                                                    style='vertical-align: middle;' />
                                                            </span>
                                                            <span id='user{{ $row['id'] }}'></span>
                                                        @else
                                                            <span id='userImage{{ $row['id'] }}'>
                                                                <img src='{{ BASE_URL }}/api/users?profileImage=false'
                                                                    width='25'
                                                                    style='vertical-align: middle;' />
                                                            </span>
                                                            <span id='user{{ $row['id'] }}'></span>
                                                        @endif
                                                    </span>
                                                </a>
                                                <ul class="dropdown-menu"
                                                    aria-labelledby="userDropdownMenuLink{{ $row['id'] }}">
                                                    <li class="nav-header border">
                                                        {{ __('dropdown.choose_user') }}</li>
                                                    @foreach ($users as $user)
                                                        <li class='dropdown-item'>
                                                            <a href='javascript:void(0);'
                                                                data-label='{{ sprintf(__('text.full_name'), $user['firstname'], $user['lastname']) }}'
                                                                data-value='{{ $row['id'] . '_' . $user['id'] . '_' . $user['profileId'] }}'
                                                                id='userStatusChange{{ $row['id'] . $user['id'] }}'>
                                                                <img src='{{ BASE_URL }}/api/users?profileImage={{ $user['id'] }}'
                                                                    width='25'
                                                                    style='vertical-align: middle; margin-right:5px;' />
                                                                {{ sprintf(__('text.full_name'), $user['firstname'], $user['lastname']) }}
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>

                                            <div class="right" style="margin-right:10px;">
                                                <a href="#/goalcanvas/editCanvasComment/{{ $row['id'] }}"
                                                    class="commentCountLink"
                                                    data="item_{{ $row['id'] }}"><span
                                                        class="fas fa-comments"></span></a>
                                                <small>{{ $nbcomments }}</small>
                                            </div>

                                        </div>
                                    </div>

                                    @if ($row['milestoneHeadline'] != '')
                                        <br />
                                        <div hx-trigger="load" hx-indicator=".htmx-indicator"
                                            hx-get="{{ BASE_URL }}/hx/tickets/milestones/showCard?milestoneId={{ $row['milestoneId'] }}">
                                            <div class="htmx-indicator">
                                                {{ __('label.loading_milestone') }}
                                            </div>
                                        </div>
                                    @endif
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
                        <a href='javascript:void(0)' class='addCanvasLink btn btn-primary'>
                            {{ __('links.icon.create_new_board') }}
                        </a>
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
            if (jQuery('#searchCanvas').length > 0) {
                new SlimSelect({
                    select: '#searchCanvas'
                });
            }

            leantime.goalCanvasController.setRowHeights();
            leantime.canvasController.setCanvasName('goal');
            leantime.canvasController.initFilterBar();

            @if ($login::userIsAtLeast($roles::$editor))
                leantime.canvasController.initCanvasLinks();
                leantime.canvasController.initUserDropdown();
                leantime.canvasController.initStatusDropdown();
                leantime.canvasController.initRelatesDropdown();
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
