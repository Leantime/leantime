@extends($layout)
@section('content')
    @php
        $elementName = 'goal';

        use Leantime\Domain\Comments\Repositories\Comments;

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
                    @php
                        $labelText =
                            '<a href="javascript:void(0)" class="dropdown-toggle btn btn-transparent" data-toggle="dropdown"><i class="fa-solid fa-ellipsis-v"></i></a>';
                    @endphp

                    <x-global::content.context-menu :labelText="$labelText" class="editCanvasDropdown" align="start"
                        contentRole="menu">
                        @if ($login::userIsAtLeast($roles::$editor))
                            <x-global::actions.dropdown.item href="#/goalcanvas/bigRock/{{ $currentCanvas }}">
                                {!! __('links.icon.edit') !!}
                            </x-global::actions.dropdown.item>

                            <x-global::actions.dropdown.item href="javascript:void(0)" class="cloneCanvasLink">
                                {!! __('links.icon.clone') !!}
                            </x-global::actions.dropdown.item>

                            <x-global::actions.dropdown.item href="javascript:void(0)" class="mergeCanvasLink">
                                {!! __('links.icon.merge') !!}
                            </x-global::actions.dropdown.item>

                            <x-global::actions.dropdown.item href="javascript:void(0)" class="importCanvasLink">
                                {!! __('links.icon.import') !!}
                            </x-global::actions.dropdown.item>
                        @endif

                        <x-global::actions.dropdown.item href="{{ BASE_URL }}/goalcanvas/export/{{ $currentCanvas }}">
                            {!! __('links.icon.export') !!}
                        </x-global::actions.dropdown.item>

                        <x-global::actions.dropdown.item href="javascript:window.print();">
                            {!! __('links.icon.print') !!}
                        </x-global::actions.dropdown.item>

                        @if ($login::userIsAtLeast($roles::$editor))
                            <x-global::actions.dropdown.item href="#/goalcanvas/delCanvas/{{ $currentCanvas }}"
                                class="delete">
                                {!! __('links.icon.delete') !!}
                            </x-global::actions.dropdown.item>
                        @endif
                    </x-global::content.context-menu>

                </span>
            @endif
            <h1>{{ __('headline.goal.board') }} //
                @if (count($allCanvas) > 0)
                    <span class="dropdown dropdownWrapper">
                        @php
                            $labelText = $canvasTitle . '&nbsp;<i class="fa fa-caret-down"></i>';
                        @endphp

                        <x-global::actions.dropdown :labelText="html_entity_decode($labelText)" class="header-title-dropdown" align="start"
                            contentRole="menu">
                            <x-slot:menu>
                                @if ($login::userIsAtLeast($roles::$editor))
                                    <x-global::actions.dropdown.item href="#/goalcanvas/bigRock">
                                        {{ __('links.icon.create_new_bigrock') }}
                                    </x-global::actions.dropdown.item>
                                @endif

                                <li class="border"></li>

                                @foreach ($allCanvas as $canvasRow)
                                    <x-global::actions.dropdown.item
                                        href="{{ BASE_URL }}/goalcanvas/showCanvas/{{ $canvasRow['id'] }}">
                                        {{ $canvasRow['title'] }}
                                    </x-global::actions.dropdown.item>
                                @endforeach
                            </x-slot:menu>
                        </x-global::actions.dropdown>

                    </span>
                @endif
            </h1>
        </div>
    </div>
    <!--pageheader-->

    <div class="maincontent">
        <div class="maincontentinner">

            @displayNotification()

            <div class="row">
                <div class="col-md-3">
                    @if ($login::userIsAtLeast($roles::$editor) && count($canvasTypes) == 1 && count($allCanvas) > 0)
                        <x-global::forms.button href="#/goalcanvas/editCanvasItem?type={{ $elementName }}"
                            id="{{ $elementName }}">
                            {!! __('links.add_new_canvas_itemgoal') !!}
                        </x-global::forms.button>
                    @endif
                </div>

                <div class="col-md-6 center">
                </div>

                <div class="col-md-3">
                    <div class="pull-right">
                        <div class="btn-group viewDropDown">
                            @if (count($allCanvas) > 0 && !empty($statusLabels))
                            <x-global::actions.dropdown class="btn dropdown-toggle" align="start" contentRole="menu">
                                <!-- Dropdown Trigger -->
                                <x-slot:labelText>
                                    @if ($filter['status'] == 'all')
                                        <i class="fas fa-filter"></i> {!! __('status.all') !!} {!! __('links.view') !!}
                                    @else
                                        <i class="fas fa-fw {!! __($statusLabels[$filter['status']]['icon']) !!}"></i> 
                                        {!! $statusLabels[$filter['status']]['title'] !!} {!! __('links.view') !!}
                                    @endif
                                </x-slot:labelText>
                            
                                <!-- Menu Slot -->
                                <x-slot:menu>
                                    <!-- "All" Status Option -->
                                    <x-global::actions.dropdown.item 
                                        href="{{ BASE_URL }}/goalcanvas/showCanvas?filter_status=all"
                                        class="{{ $filter['status'] == 'all' ? 'active' : '' }}">
                                        <i class="fas fa-globe"></i> {{ __('status.all') }}
                                    </x-global::actions.dropdown.item>
                            
                                    <!-- Dynamic Status Options -->
                                    @foreach ($statusLabels as $key => $data)
                                        <x-global::actions.dropdown.item 
                                            href="{{ BASE_URL }}/goalcanvas/showCanvas?filter_status={{ $key }}"
                                            class="{{ $filter['status'] == $key ? 'active' : '' }}">
                                            <i class="fas fa-fw {{ $data['icon'] }}"></i> {{ $data['title'] }}
                                        </x-global::actions.dropdown.item>
                                    @endforeach
                                </x-slot:menu>
                            </x-global::actions.dropdown>
                            
                            @endif
                        </div>

                        <div class="btn-group viewDropDown">
                            @if (count($allCanvas) > 0 && !empty($relatesLabels))
                            <x-global::actions.dropdown contentRole="link" position="bottom" align="start" class="btn dropdown-toggle">
                                <!-- Dropdown Trigger -->
                                <x-slot:labelText>
                                    @if ($filter['relates'] == 'all')
                                        <i class="fas fa-fw fa-globe"></i> {!! __('relates.all') !!} {!! __('links.view') !!}
                                    @else
                                        <i class="fas fa-fw {!! __($relatesLabels[$filter['relates']]['icon']) !!}"></i> 
                                        {!! $relatesLabels[$filter['relates']]['title'] !!} {!! __('links.view') !!}
                                    @endif
                                </x-slot:labelText>
                            
                                <!-- Menu Slot -->
                                <x-slot:menu>
                                    <!-- 'All' Filter Option -->
                                    <x-global::actions.dropdown.item variant="link"
                                        href="{{ BASE_URL }}/goalcanvas/showCanvas?filter_relates=all"
                                        :class="$filter['relates'] == 'all' ? 'active' : ''">
                                        <i class="fas fa-globe"></i> {!! __('relates.all') !!}
                                    </x-global::actions.dropdown.item>
                            
                                    <!-- Dynamic Relates Options -->
                                    @foreach ($relatesLabels as $key => $data)
                                        <x-global::actions.dropdown.item variant="link"
                                            href="{{ BASE_URL }}/goalcanvas/showCanvas?filter_relates={{ $key }}"
                                            :class="$filter['relates'] == $key ? 'active' : ''">
                                            <i class="fas fa-fw {{ $data['icon'] }}"></i> {{ $data['title'] }}
                                        </x-global::actions.dropdown.item>
                                    @endforeach
                                </x-slot:menu>
                            </x-global::actions.dropdown>
                            
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
                                            $comments = app()->make(Comments::class);
                                            $nbcomments = $comments->countComments(moduleId: $row['id']);
                                        @endphp
                                        <div class="col-md-4">
                                            <div class="ticketBox" id="item_{{ $row['id'] }}">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="inlineDropDownContainer" style="float:right;">
                                                            @if ($login::userIsAtLeast($roles::$editor))
                                                                <x-global::actions.dropdown
                                                                    label-text="<i class='fa fa-ellipsis-v' aria-hidden='true'></i>"
                                                                    contentRole="link" position="bottom" align="start"
                                                                    class="ticketDropDown">

                                                                    <x-slot:menu>
                                                                        <!-- Menu Header -->
                                                                        <x-global::actions.dropdown.item variant="nav-header">
                                                                            {{ __('subtitles.edit') }}
                                                                        </x-global::actions.dropdown.item>
                                                                        

                                                                        <!-- Edit Canvas Item -->
                                                                        <x-global::actions.dropdown.item variant="link"
                                                                            href="#/goalcanvas/editCanvasItem/{{ $row['id'] }}"
                                                                            :data="'item_' . $row['id']">
                                                                            {!! __('links.edit_canvas_item') !!}
                                                                        </x-global::actions.dropdown.item>

                                                                        <!-- Delete Canvas Item -->
                                                                        <x-global::actions.dropdown.item variant="link"
                                                                            href="#/goalcanvas/delCanvasItem/{{ $row['id'] }}"
                                                                            :data="'item_' . $row['id']">
                                                                            {!! __('links.delete_canvas_item') !!}
                                                                        </x-global::actions.dropdown.item>
                                                                    </x-slot:menu>

                                                                </x-global::actions.dropdown>
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
                                                                @php
                                                                    // Determine the label content dynamically
                                                                    $labelContent =
                                                                        "<span class='text'>" .
                                                                        ($row['status'] != ''
                                                                            ? $statusLabels[$row['status']]['title']
                                                                            : '') .
                                                                        "</span> <i class='fa fa-caret-down' aria-hidden='true'></i>";
                                                                @endphp

                                                                <x-global::actions.dropdown :label-text="$labelContent"
                                                                    contentRole="link" position="bottom" align="start"
                                                                    class="f-left status label-{{ $row['status'] != '' ? $statusLabels[$row['status']]['dropdown'] : '' }}"
                                                                    id="statusDropdownMenuLink{{ $row['id'] }}">

                                                                    <x-slot:menu>
                                                                        <!-- Menu Header -->
                                                                        <x-global::actions.dropdown.item variant="nav-header-border">
                                                                            {{ __('dropdown.choose_status') }}
                                                                        </x-global::actions.dropdown.item>
                                                                        

                                                                        <!-- Dynamic Status Menu Items -->
                                                                        @foreach ($statusLabels as $key => $data)
                                                                            @if ($data['active'] || true)
                                                                                <x-global::actions.dropdown.item
                                                                                    variant="link"
                                                                                    href="javascript:void(0);"
                                                                                    class="label-{{ $data['dropdown'] }}"
                                                                                    :data-label="$data['title']" :data-value="$row['id'] . '/' . $key"
                                                                                    :id="'ticketStatusChange' .
                                                                                        $row['id'] .
                                                                                        $key">
                                                                                    {{ $data['title'] }}
                                                                                </x-global::actions.dropdown.item>
                                                                            @endif
                                                                        @endforeach
                                                                    </x-slot:menu>

                                                                </x-global::actions.dropdown>


                                                            </div>
                                                        @endif


                                                        <div
                                                            class="dropdown ticketDropdown userDropdown noBg show right lastDropdown dropRight">
                                                            @php
                                                                $userImageUrl =
                                                                    $row['authorFirstname'] != ''
                                                                        ? BASE_URL .
                                                                            '/api/users?profileImage=' .
                                                                            $row['author']
                                                                        : BASE_URL . '/api/users?profileImage=false';
                                                                $labelText =
                                                                    '<span class="text">
                                                                            <span id="userImage' .
                                                                    $row['id'] .
                                                                    '">
                                                                                <img src="' .
                                                                    $userImageUrl .
                                                                    '" width="25" style="vertical-align: middle;" />
                                                                            </span>
                                                                            <span id="user' .
                                                                    $row['id'] .
                                                                    '"></span>
                                                                         </span>';
                                                            @endphp

                                                            <x-global::actions.dropdown :labelText="html_entity_decode($labelText)" class="f-left"
                                                                align="start" contentRole="menu">
                                                                <x-slot:menu>
                                                                    <x-global::actions.dropdown.item variant="nav-header-border">
                                                                        {{ __('dropdown.choose_user') }}
                                                                    </x-global::actions.dropdown.item>
                                                                    

                                                                    @foreach ($users as $user)
                                                                        <x-global::actions.dropdown.item
                                                                            href="javascript:void(0);" :data-label="sprintf(
                                                                                __('text.full_name'),
                                                                                $user['firstname'],
                                                                                $user['lastname'],
                                                                            )"
                                                                            :data-value="$row['id'] .
                                                                                '_' .
                                                                                $user['id'] .
                                                                                '_' .
                                                                                $user['profileId']"
                                                                            id="userStatusChange{{ $row['id'] . $user['id'] }}">
                                                                            <img src="{{ BASE_URL }}/api/users?profileImage={{ $user['id'] }}"
                                                                                width="25"
                                                                                style="vertical-align: middle; margin-right:5px;" />
                                                                            {{ sprintf(__('text.full_name'), $user['firstname'], $user['lastname']) }}
                                                                        </x-global::actions.dropdown.item>
                                                                    @endforeach
                                                                </x-slot:menu>
                                                            </x-global::actions.dropdown>

                                                        </div>

                                                        <div class="right" style="margin-right:10px;">
                                                            <x-global::forms.button
                                                            tag="a"
                                                            href="#/goalcanvas/editCanvasComment/{{ $row['id'] }}"
                                                            class="commentCountLink"
                                                            data="item_{{ $row['id'] }}">
                                                            <span class="fas fa-comments"></span>
                                                        </x-global::forms.button>
                                                            <small>{{ $nbcomments }}</small>
                                                        </div>

                                                    </div>
                                                </div>

                                                @if ($row['milestoneHeadline'] != '')
                                                    <br />
                                                    <div hx-trigger="load" hx-indicator=".htmx-indicator"
                                                        hx-get="{{ BASE_URL }} /hx/tickets/milestones/showCard?milestoneId={{ $row['milestoneId'] }}">
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
                        <x-global::forms.button
                            tag="a"
                            href="javascript:void(0)"
                            class="addCanvasLink btn btn-primary">
                            {{ __('links.icon.create_new_board') }}
                        </x-global::forms.button>     
                    @endif
                </div>
            @endif

            @if (!empty($disclaimer) && count($allCanvas) > 0)
                <small class="align-center">{{ $disclaimer }}</small>
            @endif

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
                leantime.canvasController.initUserDropdown('goalcanvas');
                leantime.canvasController.initStatusDropdown('goalcanvas');
                leantime.canvasController.initRelatesDropdown('goalcanvas');
            @else
                leantime.authController.makeInputReadonly(".maincontentinner");
            @endif

        });
    </script>
@endsection
