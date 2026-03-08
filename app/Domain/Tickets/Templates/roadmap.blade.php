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
                    <x-globals::actions.dropdown-menu id="ganttTimeControl" variant="button" content-role="default" :label="__('buttons.timeframe') . ': ' . $currentView">
                        <x-globals::actions.dropdown-item href="javascript:void(0);" data-value="Day" :state="$roadmapView == 'Day' ? 'active' : null">{{ __('buttons.day') }}</x-globals::actions.dropdown-item>
                        <x-globals::actions.dropdown-item href="javascript:void(0);" data-value="Week" :state="$roadmapView == 'Week' ? 'active' : null">{{ __('buttons.week') }}</x-globals::actions.dropdown-item>
                        <x-globals::actions.dropdown-item href="javascript:void(0);" data-value="Month" :state="$roadmapView == 'Month' ? 'active' : null">{{ __('buttons.month') }}</x-globals::actions.dropdown-item>
                    </x-globals::actions.dropdown-menu>
                </div>
            </div>
        </div>

        @if((is_array($timelineTasks) && count($timelineTasks) == 0) || $timelineTasks == false)
            <div class="empty tw:text-center" id="emptySprint">
                <div class="svgContainer tw:w-[30%] tw:mx-auto">
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

    });

</script>
