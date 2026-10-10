@extends($layout)

@section('content')

@php
    $milestones = $milestones ?? [];
    $clients = $clients ?? [];

    $clientNameSelected = __('headline.all_clients');
    $htmlDropdownClients = '';
    foreach ($clients as $client) {
        $href = BASE_URL.'/tickets/roadmapAll?clientId='.$client['id'];
        $labelActive = '';
        if (isset($_GET['clientId']) && $_GET['clientId'] == $client['id']) {
            $labelActive = ' class="active"';
            $clientNameSelected = $client['name'];
        }
        $htmlDropdownClients .= "<li><a href='$href' $labelActive> {$client['name']} </a></li>";
    }

    $roadmapView = session('usersettings.views.roadmap', 'Month');
@endphp

@include('tickets::submodules.portfolioHeader')

<div class="maincontent">
    @include('tickets::submodules.portfolioTabs')

    <div class="maincontentinner">

        {!! $tpl->displayNotification() !!}

        <div class="row">
            <div class="col-md-6">
            </div>
            <div class="col-md-6">
                <div class="pull-right">

                    <x-global::actions.dropdown variant="filter">
                        <x-slot:trigger>{!! __('label.roles.client') !!}: <span class="viewText">{{ $clientNameSelected }}</span><span class="caret"></span></x-slot:trigger>
                        <li><a href={{ BASE_URL.'/tickets/roadmapAll' }} {{ empty($labelActive) ? "class='active'" : '' }} > {!! __('headline.all_clients') !!} </a></li>
                        {!! $htmlDropdownClients !!}

                    </x-global::actions.dropdown>

                    @php
                        $currentView = '';
                        if ($roadmapView == 'Day') {
                            $currentView = __('buttons.day');
                        } elseif ($roadmapView == 'Week') {
                            $currentView = __('buttons.week');
                        } elseif ($roadmapView == 'Month') {
                            $currentView = __('buttons.month');
                        }
                    @endphp
                    <x-global::actions.dropdown variant="filter" menu-id="ganttTimeControl" class="dropRight">
                        <x-slot:trigger>
                            {!! __('buttons.timeframe') !!}: <span class="viewText">{{ $currentView }}</span><span class="caret"></span>
                        </x-slot:trigger>
                        <li><a href="javascript:void(0);" data-value="Day" class="{{ $roadmapView == 'Day' ? 'active' : '' }}"> {!! __('buttons.day') !!}</a></li>
                        <li><a href="javascript:void(0);" data-value="Week" class="{{ $roadmapView == 'Week' ? 'active' : '' }}">{!! __('buttons.week') !!}</a></li>
                        <li><a href="javascript:void(0);" data-value="Month" class="{{ $roadmapView == 'Month' ? 'active' : '' }}">{!! __('buttons.month') !!}</a></li>

                    </x-global::actions.dropdown>

                </div>

            </div>
        </div>

        @php
        if (count($milestones) == 0) {
            echo "<div class='empty' id='emptySprint' style='text-align:center;'>";
            echo "<div style='width:30%' class='svgContainer'>";
            echo file_get_contents(ROOT.'/dist/images/svg/undraw_adjustments_p22m.svg');
            echo '</div>';
            echo '<h4>'.__('headlines.no_milestones').'<br/>
            <br />
            <a href="'.BASE_URL.'/tickets/editMilestone" class="milestoneModal addCanvasLink btn btn-primary">'.__('links.add_milestone').'</a></h4></div>';
        }
        @endphp
        <div class="gantt-wrapper">
            <svg id="gantt"></svg>
        </div>

    </div>
</div>

@once @push('scripts')
<script type="text/javascript">

    jQuery(document).ready(function(){


    @if (isset($_GET['showMilestoneModal']))
        @php
            $modalUrl = $_GET['showMilestoneModal'] == '' ? '' : '/'.(int) $_GET['showMilestoneModal'];
        @endphp

        leantime.ticketsController.openMilestoneModalManually("{{ BASE_URL }}/tickets/editMilestone{{ $modalUrl }}");
        window.history.pushState({},document.title, '{{ BASE_URL }}/tickets/roadmap');

    @endif


});

    @if (count($milestones) > 0)
        var tasks = [

            @php
            foreach ($milestones as $mlst) {
                $headline = '['.$mlst->projectName.'] ';
                $headline .= __('label.'.strtolower($mlst->type)).': '.$mlst->headline;
                if ($mlst->type == 'milestone') {
                    $headline .= ' ('.$mlst->percentDone.'% Done)';
                }

                $color = '#8D99A6';
                if ($mlst->type == 'milestone') {
                    $color = $mlst->tags;
                }

                $sortIndex = 0;
                if ($mlst->sortIndex != '' && is_numeric($mlst->sortIndex)) {
                    $sortIndex = $mlst->sortIndex;
                }

                $dependencyList = [];
                if ($mlst->milestoneid != 0) {
                    $dependencyList[] = $mlst->milestoneid;
                }

                if ($mlst->dependingTicketId != 0) {
                    $dependencyList[] = $mlst->dependingTicketId;
                }

                // Every value goes through json_encode with the HEX flags so names and colors can
                // neither break out of the JS string nor close the surrounding <script> element.
                $jsFlags = JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP;
                $startDate = ($mlst->editFrom != '0000-00-00 00:00:00' && ! str_starts_with((string) $mlst->editFrom, '1969-12-31')) ? $mlst->editFrom : dtHelper()->userNow()->addDay()->format('Y-m-d');
                $endDate = ($mlst->editTo != '0000-00-00 00:00:00' && ! str_starts_with((string) $mlst->editTo, '1969-12-31')) ? $mlst->editTo : dtHelper()->userNow()->addWeek()->format('Y-m-d');

                echo '{
                    projectName: '.json_encode((string) $mlst->projectName, $jsFlags).',
                    id: '.json_encode((string) $mlst->id, $jsFlags).',
                    name: '.json_encode($headline, $jsFlags).',
                    start: '.json_encode((string) $startDate, $jsFlags).',
                    end: '.json_encode((string) $endDate, $jsFlags).',
                    progress: '.json_encode((string) $mlst->percentDone, $jsFlags).',
                    dependencies: '.json_encode(implode(',', $dependencyList), $jsFlags).",
                    custom_class: '',
                    type: ".json_encode(strtolower((string) $mlst->type), $jsFlags).',
                    bg_color: '.json_encode((string) $color, $jsFlags).',
                    thumbnail: '.json_encode(BASE_URL.'/api/users?profileImage='.$mlst->editorId, $jsFlags).',
                    sortIndex: '.$sortIndex.'
                },';
            }
            @endphp
        ];

        @if ($login::userIsAtLeast($roles::$editor))
        leantime.ticketsController.initGanttChart(tasks, '{{ $roadmapView }}', false);
        @else
        leantime.ticketsController.initGanttChart(tasks, '{{ $roadmapView }}', true);
        @endif

    @endif



</script>
@endpush @endonce

@endsection
