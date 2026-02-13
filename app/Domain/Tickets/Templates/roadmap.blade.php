@php
    $milestones = $tpl->get('milestones');
    $timelineTasks = $tpl->get('timelineTasks');
    $roadmapView = session('usersettings.views.roadmap', 'Month');
@endphp

{!! $tpl->displayNotification() !!}

@php $tpl->displaySubmodule('tickets-timelineHeader') @endphp

<div class="maincontent">

    @php $tpl->displaySubmodule('tickets-timelineTabs') @endphp

    <div class="maincontentinner">

        <div class="row">
            <div class="col-md-4">
                @dispatchEvent('filters.afterLefthandSectionOpen')
                @php
                    $tpl->displaySubmodule('tickets-ticketNewBtn');
                    $tpl->displaySubmodule('tickets-ticketFilter');
                @endphp
                @dispatchEvent('filters.beforeLefthandSectionClose')
            </div>
            <div class="col-md-4">
            </div>
            <div class="col-md-4">
                <div class="pull-right">
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

        @if((is_array($timelineTasks) && count($timelineTasks) == 0) || $timelineTasks == false)
            <div class="empty" id="emptySprint" style="text-align:center;">
                <div style="width:30%" class="svgContainer">
                    {!! file_get_contents(ROOT . '/dist/images/svg/undraw_adjustments_p22m.svg') !!}
                </div>
                <h4>{{ __('headlines.no_tickets') }}<br /></h4>
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

    });

    @if(count($timelineTasks) > 0)
        var tasks = [
            @php
                $lastMilestoneSortIndex = [];
                foreach ($timelineTasks as $mlst) {
                    if ($mlst->type == 'milestone') {
                        $lastMilestoneSortIndex[$mlst->id] = ($mlst->sortIndex != '') ? $mlst->sortIndex : 999;
                    }
                }

                foreach ($timelineTasks as $mlst) {
                    $headline = __('label.' . strtolower($mlst->type)) . ': ' . $mlst->headline;
                    if ($mlst->type == 'milestone') {
                        $headline .= ' (' . format($mlst->percentDone)->decimal() . '% Done)';
                    }

                    $color = '#8D99A6';
                    if ($mlst->type == 'milestone') {
                        $color = $mlst->tags;
                    }

                    $sortIndex = $mlst->sortIndex;
                    $dependencyList = [];
                    if ($mlst->dependingTicketId != 0) {
                        $dependencyList[] = $mlst->dependingTicketId;
                    } elseif ($mlst->milestoneid != 0) {
                        $dependencyList[] = $mlst->milestoneid;
                    }

                    echo "{
                        id :'" . $mlst->id . "',
                        name :" . json_encode($headline) . ",
                        start :'" . (dtHelper()->isValidDateString($mlst->editFrom) ? $mlst->editFrom : dtHelper()->userNow()->addDays(2)->format('Y-m-d')) . "',
                        end :'" . (dtHelper()->isValidDateString($mlst->editTo) ? $mlst->editTo : dtHelper()->userNow()->addDays(2)->format('Y-m-d')) . "',
                        progress :'" . format($mlst->percentDone)->decimal() . "',
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

</script>
