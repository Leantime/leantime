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
            background: var(--color-bg-card) !important;
            border-color: var(--color-bg-card) !important;
        }

        div.canvas-element-center-middle {
            text-align: center;
        }
    </style>

    <div class="pageheader">
        <div class="pageicon"><x-global::elements.icon :name="$canvasIcon" /></div>
        <div class="pagetitle">
            <h5>{{ session('currentProjectClient') . ' // ' . session('currentProjectName') }}</h5>
            @if (count($allCanvas) > 0)
                <x-globals::actions.dropdown-menu container-class="headerEditDropdown">
                    @if ($login::userIsAtLeast($roles::$editor))
                        <li><a href="#/goalcanvas/bigRock/{{ $currentCanvas }}">{!! __('links.icon.edit') !!}</a></li>
                        <li><a href="javascript:void(0)" class="cloneCanvasLink ">{!! __('links.icon.clone') !!}</a></li>
                        <li><a href="javascript:void(0)" class="mergeCanvasLink ">{!! __('links.icon.merge') !!}</a></li>
                        <li><a href="javascript:void(0)" class="importCanvasLink ">{!! __('links.icon.import') !!}</a></li>
                    @endif
                    <li><a href="{{ BASE_URL }}/goalcanvas/export/{{ $currentCanvas }}" hx-boost="false">{!! __('links.icon.export') !!}</a></li>
                    <li><a href="javascript:window.print();">{!! __('links.icon.print') !!}</a></li>
                    @if ($login::userIsAtLeast($roles::$editor))
                        <li><a href="#/goalcanvas/delCanvas/{{ $currentCanvas }}" class="delete">{!! __('links.icon.delete') !!}</a></li>
                    @endif
                </x-globals::actions.dropdown-menu>
            @endif
            <h1>{{ __('headline.goal.board') }} //
                @if (count($allCanvas) > 0)
                    <x-globals::actions.dropdown-menu variant="link" trailing-visual="arrow_drop_down" :label="$canvasTitle" trigger-class="header-title-dropdown">
                        @if ($login::userIsAtLeast($roles::$editor))
                            <li><a href="#/goalcanvas/bigRock">{!! __('links.icon.create_new_bigrock') !!}</a></li>
                        @endif
                        <li class="border"></li>
                        @foreach ($allCanvas as $canvasRow)
                            <li><a href='{{ BASE_URL }}/goalcanvas/showCanvas/{{ $canvasRow['id'] }}'>{{ $canvasRow['title'] }}</a></li>
                        @endforeach
                    </x-globals::actions.dropdown-menu>
                @endif
            </h1>
        </div>
    </div>
    <!--pageheader-->

    <div class="maincontent">
        <div class="maincontentinner">

            <?php echo $tpl->displayNotification(); ?>

            <div class="tw:flex tw:justify-between tw:items-center">
                <div>
                    @if ($login::userIsAtLeast($roles::$editor) && count($canvasTypes) == 1 && count($allCanvas) > 0)
                        <x-globals::forms.button link="#/goalcanvas/editCanvasItem?type={{ $elementName }}&canvasId={{ $currentCanvas }}" type="primary" id="{{ $elementName }}">{!! __('links.add_new_canvas_itemgoal') !!}</x-globals::forms.button>
                    @endif
                </div>

                <div>
                </div>

                <div>
                    <div class="pull-right">
                        @if (count($allCanvas) > 0 && !empty($statusLabels))
                            @php
                                $filterStatus = $filter['status'] ?? 'all';
                                if ($filterStatus != 'all' && !isset($statusLabels[$filterStatus])) { $filterStatus = 'all'; }
                                $filterRelates = $filter['relates'] ?? 'all';
                                $statusFilterLabel = $filterStatus == 'all'
                                    ? '<x-global::elements.icon name="filter_list" /> ' . __('status.all')
                                    : '<span class="material-symbols-outlined">' . __($statusLabels[$filterStatus]['icon']) . '</span> ' . $statusLabels[$filterStatus]['title'];
                            @endphp
                            <x-globals::actions.dropdown-menu variant="button" :label="$statusFilterLabel" content-role="default">
                                <li><a href="{{ BASE_URL }}/goalcanvas/showCanvas?filter_status=all" @if ($filterStatus == 'all') class="active" @endif><x-global::elements.icon name="language" /> {!! __('status.all') !!}</a></li>
                                @foreach ($statusLabels as $key => $data)
                                    <li><a href="{{ BASE_URL }}/goalcanvas/showCanvas?filter_status={{ $key }}" @if ($filterStatus == $key) class="active" @endif><x-global::elements.icon :name="$data['icon']" /> {!! $data['title'] !!}</a></li>
                                @endforeach
                            </x-globals::actions.dropdown-menu>
                        @endif

                        @if (count($allCanvas) > 0 && !empty($relatesLabels))
                            @php
                                $filterRelates = $filter['relates'] ?? 'all';
                                if ($filterRelates != 'all' && !isset($relatesLabels[$filterRelates])) { $filterRelates = 'all'; }
                                $relatesFilterLabel = $filterRelates == 'all'
                                    ? '<x-global::elements.icon name="language" /> ' . __('relates.all')
                                    : '<span class="material-symbols-outlined">' . __($relatesLabels[$filterRelates]['icon']) . '</span> ' . $relatesLabels[$filterRelates]['title'];
                            @endphp
                            <x-globals::actions.dropdown-menu variant="button" :label="$relatesFilterLabel" content-role="default">
                                <li><a href="{{ BASE_URL }}/goalcanvas/showCanvas?filter_relates=all" @if ($filterRelates == 'all') class="active" @endif><x-global::elements.icon name="language" /> {{ __('relates.all') }}</a></li>
                                @foreach ($relatesLabels as $key => $data)
                                    <li><a href="{{ BASE_URL }}/goalcanvas/showCanvas?filter_relates={{ $key }}" @if ($filterRelates == $key) class="active" @endif><x-global::elements.icon :name="$data['icon']" /> {{ $data['title'] }}</a></li>
                                @endforeach
                            </x-globals::actions.dropdown-menu>
                        @endif
                    </div>
                </div>
            </div>

            <div class="clearfix"></div>


            @if (count($allCanvas) > 0)
                <div id="sortableCanvasKanban" class="sortableTicketList disabled tw:pt-4">
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
                                        <div class="col-md-4">
                                            <x-globals::goals.goal-card :row="$row" :status-labels="$statusLabels" :relates-labels="$relatesLabels" :users="$users" :element-name="$elementName" />
                                        </div>
                        @endif
                    @endforeach
                </div>
                <br />
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
                        <x-globals::forms.button link="javascript:void(0)" type="primary" class="addCanvasLink">
                            {!! __('links.icon.create_new_board') !!}
                        </x-globals::forms.button>
                    @endif
                </div>
            @endif

            @if (!empty($disclaimer) && count($allCanvas) > 0)
                <small class="center">{{ $disclaimer }}</small>
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
