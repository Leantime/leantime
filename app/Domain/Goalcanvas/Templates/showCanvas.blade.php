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
                @include('goalcanvas::partials.goalBoard')
            </h1>
        </div>
    </div>
    <!--pageheader-->

    <div class="maincontent">
        <div class="maincontentinner">

            @displayNotification()

            <div class="row">
                <div class="col-md-12">
                    @if ($login::userIsAtLeast($roles::$editor) && count($canvasTypes) == 1 && count($allCanvas) > 0)
                        <x-global::forms.button scale="sm" class="pull-right" tag="a"
                            href="#/goalcanvas/editCanvasItem?type={{ $elementName }}&canvasId={{ $currentCanvas }}"
                            id="{{ $elementName }}">
                            {!! __('links.add_new_canvas_itemgoal') !!}
                        </x-global::forms.button>
                    @endif
                </div>


                {{-- <div class="col-md-3">
                    <div class="pull-right">
                        <div class="btn-group viewDropDown">
                            @if (count($allCanvas) > 0 && !empty($statusLabels))
                                @php
                                    $filterStatus = $filter['status'] ?? 'all';
                                    $filterRelates = $filter['relates'] ?? 'all';
                                @endphp
                                <x-global::actions.dropdown align="start" contentRole="ghost">
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
                                @php
                                    $filterStatus = $filter['status'] ?? 'all';
                                    $filterRelates = $filter['relates'] ?? 'all';
                                @endphp
                                <x-global::actions.dropdown contentRole="link" position="bottom" align="start"
                                    class="btn dropdown-toggle">
                                    <!-- Dropdown Trigger -->
                                    <x-slot:labelText>
                                        @if ($filter['relates'] == 'all')
                                            <i class="fas fa-fw fa-globe"></i> {!! __('relates.all') !!}
                                            {!! __('links.view') !!}
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
                </div> --}}
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

                                    <x-goalcanvas::goal-item-card :row="$row" elementName="goal" :filter="$filter ?? []"
                                        :statusLabels="$statusLabels" :relatesLabels="$relatesLabels" :users="$users" />
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
                        <x-global::forms.button tag="a" href="javascript:void(0)"
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
