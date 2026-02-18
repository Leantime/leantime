@php
    $milestones = $tpl->get('milestones');
    $clients = $tpl->get('clients');

    $clientNameSelected = __('headline.all_clients');
    $htmlDropdownClients = '';
    foreach ($clients as $client) {
        $href = BASE_URL . '/tickets/roadmapAll?clientId=' . $client['id'];
        $labelActive = '';
        if (isset($_GET['clientId']) && $_GET['clientId'] == $client['id']) {
            $labelActive = ' class="active"';
            $clientNameSelected = $client['name'];
        }
        $htmlDropdownClients .= "<li><a href='$href' $labelActive> {$client['name']} </a></li>";
    }

    $roadmapView = session('usersettings.views.roadmap', 'Month');
@endphp

@php $tpl->displaySubmodule('tickets-portfolioHeader') @endphp

<div class="maincontent">
    @php $tpl->displaySubmodule('tickets-portfolioTabs') @endphp

    <div class="maincontentinner">

        {!! $tpl->displayNotification() !!}

        <div class="tw:flex tw:justify-between tw:items-start">
            <div>
            </div>
            <div>
                <div class="tw:float-right">

                    <div class="btn-group viewDropDown">
                        <button class="btn dropdown-toggle" data-toggle="dropdown">{{ __('label.roles.client') }}: <span class="viewText">{{ $clientNameSelected }}</span><span class="caret"></span></button>
                        <ul class="dropdown-menu">
                            <li><a href="{{ BASE_URL }}/tickets/roadmapAll" {!! empty($labelActive) ? "class='active'" : '' !!}> {{ __('headline.all_clients') }} </a></li>
                            {!! $htmlDropdownClients !!}
                        </ul>
                    </div>

                    <div class="btn-group dropRight">
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
                        <button class="btn dropdown-toggle" data-toggle="dropdown">{{ __('buttons.timeframe') }}: <span class="viewText">{{ $currentView }}</span><span class="caret"></span></button>
                        <ul class="dropdown-menu" id="ganttTimeControl">
                            <li><a href="javascript:void(0);" data-value="Day" class="{{ $roadmapView == 'Day' ? 'active' : '' }}"> {{ __('buttons.day') }}</a></li>
                            <li><a href="javascript:void(0);" data-value="Week" class="{{ $roadmapView == 'Week' ? 'active' : '' }}">{{ __('buttons.week') }}</a></li>
                            <li><a href="javascript:void(0);" data-value="Month" class="{{ $roadmapView == 'Month' ? 'active' : '' }}">{{ __('buttons.month') }}</a></li>
                        </ul>
                    </div>

                </div>
            </div>
        </div>

        @if(count($milestones) == 0)
            <div class="empty" id="emptySprint" style="text-align:center;">
                <div style="width:30%" class="svgContainer">
                    {!! file_get_contents(ROOT . '/dist/images/svg/undraw_adjustments_p22m.svg') !!}
                </div>
                <h4>{{ __('headlines.no_milestones') }}<br/>
                <br />
                <x-global::button link="{{ BASE_URL }}/tickets/editMilestone" type="primary" class="milestoneModal addCanvasLink">{{ __('links.add_milestone') }}</x-global::button></h4>
            </div>
        @endif

        <div class="gantt-wrapper">
            <svg id="gantt"></svg>
        </div>

    </div>
</div>

<script type="text/javascript">

    jQuery(document).ready(function(){

    @if(isset($_GET['showMilestoneModal']))
        @php
            $modalUrl = $_GET['showMilestoneModal'] == '' ? '' : '/' . (int)$_GET['showMilestoneModal'];
        @endphp
        leantime.ticketsController.openMilestoneModalManually("{{ BASE_URL }}/tickets/editMilestone{{ $modalUrl }}");
        window.history.pushState({},document.title, '{{ BASE_URL }}/tickets/roadmap');
    @endif

    @if(count($milestones) > 0)
        var tasks = [
            @php
                foreach ($milestones as $mlst) {
                    $headline = '[' . $mlst->projectName . '] ';
                    $headline .= __('label.' . strtolower($mlst->type)) . ': ' . $mlst->headline;
                    if ($mlst->type == 'milestone') {
                        $headline .= ' (' . $mlst->percentDone . '% Done)';
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

                    echo "{
                        projectName :'" . $mlst->projectName . "',
                        id :'" . $mlst->id . "',
                        name :" . json_encode($headline) . ",
                        start :'" . (($mlst->editFrom != '0000-00-00 00:00:00' && !str_starts_with($mlst->editFrom, '1969-12-31')) ? $mlst->editFrom : date('Y-m-d', strtotime('+1 day', time()))) . "',
                        end :'" . (($mlst->editTo != '0000-00-00 00:00:00' && !str_starts_with($mlst->editTo, '1969-12-31')) ? $mlst->editTo : date('Y-m-d', strtotime('+1 week', time()))) . "',
                        progress :'" . $mlst->percentDone . "',
                        dependencies :'" . implode(',', $dependencyList) . "',
                        custom_class :'',
                        type: '" . strtolower($mlst->type) . "',
                        bg_color: '" . $color . "',
                        thumbnail: '" . BASE_URL . "/api/users?profileImage=" . $mlst->editorId . "',
                        sortIndex: " . $sortIndex . "
                    },";
                }
            @endphp
        ];

        @if($login::userIsAtLeast($roles::$editor))
        leantime.ticketsController.initGanttChart(tasks, '{{ $roadmapView }}', false);
        @else
        leantime.ticketsController.initGanttChart(tasks, '{{ $roadmapView }}', true);
        @endif
    @endif

    });

</script>
