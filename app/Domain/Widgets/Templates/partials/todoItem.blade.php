@props([
    'ticket',
    'statusLabels',
    'onTheClock',
    'tpl',
    'level' => 0
])

@php
    $ticketDataJson = json_encode([
        'id' =>  $ticket['id'] ,
        'title' =>  $ticket['headline'],
        'color' =>   'var(--accent2)',
        'enitityType' =>  'ticket',
        'url' =>  '#/tickets/showTicket/'.$ticket['id'],
    ]);

    $hasChildren = !empty($ticket['children']);
@endphp

<div class="sortable-item draggable-todo"
    id="ticket_{{ $ticket['id'] }}"
    data-item-type="{{ $ticket['type'] == 'milestone' ? 'milestone' : ($ticket['type'] == 'subtask' ? 'subtask' : 'task') }}"
    data-id="{{ $ticket['id'] }}"
    data-project="{{ $ticket['projectId'] }}"
    data-sort-index="{{ $ticket['sortIndex'] }}"
    data-event='{!! $ticketDataJson !!}' >

    @php
        $accordionId = 'task-children-'.$ticket['id'];
        $accordionState = $tpl->getToggleState($tpl->getToggleState("accordion_content-".$accordionId) == 'closed' ? 'closed' : 'open');
    @endphp

    <div class="ticketBox {{ $ticket['type'] == 'milestone' ? 'milestone-box priority-border- ' : 'priority-border-'.$ticket['priority'] }} {{ $hasChildren ? 'has-children' : '' }}"
         data-val="{{ $ticket['id'] }}"
         @if($ticket['type'] == 'milestone')
             style="background: var(--secondary-background) linear-gradient(135deg, {{ $ticket['tags'] }} 0%, var(--accent1) 100%); background-repeat: no-repeat; background-size: 100% 5px; background-position: bottom;"
             @endif
    >

        @if($hasChildren)
            <div id="accordion_toggle_{{$accordionId }}"
                 class="task-collapse-toggle accordion-toggle {{ $accordionState }}"
                 onclick="leantime.snippets.accordionToggle('{{ $accordionId}}');"
            >
                <i class="fa fa-angle-{{ $accordionState == 'closed' ? 'right' : 'down' }}"></i>
            </div>
        @endif

        @if($ticket['type'] == 'milestone')
            <div class="tw-flex tw-flex-row tw-items-center tw-gap-4">
                <div class="tw-flex-grow">
                    <small style="display:inline-block; ">{{ $ticket['projectName'] }}</small>
                    <h4><a href="#/tickets/showTicket/{{ $ticket['id'] }}" style="font-size:var(--font-size-l);">{{ $ticket['headline'] }}</a></h4>

                </div>
                <div class="tw-flex-grow">
                    <div hx-trigger="load"
                         hx-indicator=".htmx-indicator"
                         hx-get="<?=BASE_URL ?>/hx/tickets/milestones/progress?milestoneId=<?=$ticket['id'] ?>&progressColor={{ trim($ticket['tags'], "#") }}">
                        <div class="htmx-indicator">
                                <?=$tpl->__("label.loading_milestone") ?>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="tw-flex tw-flex-row">
                <div class="tw-content-center">
                    <div class="tw-content-center tw-mr-[10px]">
                        @include('tickets::partials.timerButton', ['parentTicketId' => $ticket['id'], 'onTheClock' => $onTheClock])
                    </div>
                </div>

                <div class="tw-flex-1 ticket-title ticket-title-wrapper">
                    <div class="title-text">
                       <small style="display:inline-block; ">{{ $ticket['projectName'] }}</small> <br />
                        <strong><a href="#/tickets/showTicket/{{ $ticket['id'] }}" preload="mouseover" class="ticket-headline-{{ $ticket['id'] }}">{{ $ticket['headline'] }}</a></strong>
                        &nbsp;<a href="javascript:void(0);" class="tw-hidden edit-button" data-tippy-content="{{ __('text.edit_task_headline') }}"><i class="fa fa-edit"></i></a>

                    </div>
                    <div class="tw-hidden edit-form">
                        <form class="tw-flex tw-flex-row tw-items-center tw-gap-2"
                              hx-post="{{ BASE_URL }}/hx/widgets/myToDos/updateTitle"
                              hx-target=".ticket-headline-{{ $ticket['id'] }}"
                              onsubmit="jQuery(this).closest('.edit-form').find('.cancel-edit-task').click();"
                        >
                            <input type="hidden" name="id" value="{{ $ticket['id'] }}"/>
                            <div>
                                <input type="text" class="main-title-input"
                                       style="font-size:var(--base-font-size); margin-bottom:0px" value="{{ $ticket['headline'] }}" name="headline"/>
                            </div>
                            <div>
                                <button type="submit" name="edit" class="btn btn-primary" >
                                    <i class="fa fa-check"></i>
                                </button>
                            </div>
                            <div>
                                <a href="javascript:void(0);" class="btn cancel-edit-task" data-group="{{ $groupKey }}"><i class="fa fa-x"></i></a>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="tw-flex-1 tw-justify-right tw-flex tw-flex-row tw-justify-end tw-content-center">

                    <div class="tw-content-center">
                        @if($hasChildren)
                            @php
                                $childrenCollection = collect($ticket['children']);
                                $doneTickets = $childrenCollection->where(function($ticket) {
                                    return isset($statusLabels[$ticket['projectId']][$ticket['status']]) && $statusLabels[$ticket['projectId']][$ticket['status']]['statusType'] === "DONE";
                                })->count();
                            @endphp

                            {{ $childrenCollection->count() }} open tasks
                        @endif
                    </div>
                </div>

                <div class="tw-flex-1 tw-justify-right tw-flex tw-flex-row tw-justify-end tw-content-center due-date-wrapper">
                    <div class="tw-content-center">
                        <div class="date-picker-form-control">
                        <i class="fa-solid fa-business-time infoIcon" data-tippy-content="{{ __("label.due") }}"></i>

                            <input id="due-date-picker-{{ $ticket['id'] }}"
                               type="text"
                               title="{{ __("label.due") }}"
                               value="{{ format($ticket['dateToFinish'])->date(__("text.anytime")) }}"
                               class="duedates secretInput"
                               style="margin-left:0px; width:100px;"
                               data-id="{{ $ticket['id'] }}"
                                   onchange="jQuery('#due-date-picker-trigger-{{ $ticket['id'] }}').text(this.value);"
                               name="date"
                               hx-post="{{ BASE_URL }}/widgets/myToDos/updateDueDate"
                               hx-trigger="change"
                               hx-vals='{"id": "{{ $ticket['id'] }}"}'
                               hx-indicator=".htmx-indicator" />
                        <button class="reset-button"
                                data-id="{{ $ticket['id'] }}"
                                id="reset-date-{{ $ticket['id'] }}"
                                hx-post="{{ BASE_URL }}/widgets/myToDos/updateDueDate"
                                hx-vals='{"id": "{{ $ticket['id'] }}", "date": ""}'
                                hx-indicator=".htmx-indicator">
                            <span class="sr-only">{{ __("language.resetDate") }}</span>
                            <i class="fa fa-close"></i>
                        </button>
                    </div>
                    </div>
                    <div class="tw-content-center">
                        @dispatchEvent('afterDueDate', ['ticket' => (object)$ticket])
                    </div>
                </div>
                <div class="tw-flex-1 tw-justify-items-end tw-flex tw-flex-row tw-justify-end tw-gap-2 tw-content-center">
                    <div class="tw-content-center tw-mr-[10px] dropdown ticketDropdown statusDropdown colorized show">
                        <a class="dropdown-toggle f-left status {{ $statusLabels[$ticket['projectId']][$ticket['status']]["class"] ?? 'label-default' }}"
                           href="javascript:void(0);"
                           role="button"
                           id="statusDropdownMenuLink{{ $ticket['id'] }}"
                           data-toggle="dropdown"
                           aria-haspopup="true"
                           aria-expanded="false">
                            <span class="text">
                                @if(isset($statusLabels[$ticket['projectId']][$ticket['status']]))
                                    {{ $statusLabels[$ticket['projectId']][$ticket['status']]["name"] }}
                                @else
                                    unknown
                                @endif
                            </span>
                            &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                        </a>
                        <ul class="dropdown-menu pull-right" aria-labelledby="statusDropdownMenuLink{{ $ticket['id'] }}">
                            <li class="nav-header border">{{ __("dropdown.choose_status") }}</li>
                            @foreach ($statusLabels[$ticket['projectId']] as $key => $label)
                                <li class='dropdown-item'>
                                    <a href='javascript:void(0);'
                                       class='{{ $label["class"] }}'
                                       data-label='{{ $label["name"] }}'
                                       data-value='{{ $ticket['id'] }}_{{ $key }}_{{ $label["class"] }}'
                                       id='ticketStatusChange{{$ticket['id'] . $key }}'
                                       hx-post="{{ BASE_URL }}/widgets/myToDos/updateStatus"
                                       hx-swap="none"
                                       hx-vals='{"id": "{{ $ticket['id'] }}", "status": "{{ $key }}"}'>
                                        {{ $label["name"] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="tw-content-center">
                        <div class="scheduler">
                            @if( $ticket['editFrom'] != "0000-00-00 00:00:00" && $ticket['editFrom'] != "1969-12-31 00:00:00")
                                <i class="fa-solid fa-calendar-check infoIcon" style="color:var(--accent2)" data-tippy-content="{{ __('text.schedule_to_start_on') }} {{ format($ticket['editFrom'])->date() }}"></i>
                            @else
                                <i class="fa-regular fa-calendar-xmark infoIcon" data-tippy-content="{{ __('text.not_scheduled_drag_ai') }}"></i>
                            @endif
                        </div>
                    </div>
                    <div class="tw-content-center">
                        @include("tickets::partials.ticketsubmenu", ["ticket" => $ticket, "onTheClock" => $onTheClock])
                    </div>

                </div>

            </div>
        @endif

    </div>

        <div id="accordion_content-{{ $accordionId }}"
             style="{{ $accordionState =='closed' ? 'display:none;' : '' }}"
             class="sortable-list task-children {{ $tpl->getToggleState("user.".session('userdata.id').".taskCollapsed.".$ticket['id'], 'open') }}"
             data-container-type="{{ $ticket['type'] == 'milestone' ? 'milestone' : ($ticket['type'] == 'subtask' ? 'subtask' : 'task') }}">
            @foreach($ticket['children'] as $childTicket)
                @include('widgets::partials.todoItem', ['ticket' => $childTicket, 'statusLabels' => $statusLabels, 'onTheClock' => $onTheClock, 'tpl' => $tpl, 'level' => $level + 1])
            @endforeach
        </div>

</div>
