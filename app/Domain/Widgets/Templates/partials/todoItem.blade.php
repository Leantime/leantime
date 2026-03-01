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
     id="ticket_{{ $groupKey.$ticket['id'] }}"
     data-item-type="{{ $ticket['type'] === 'milestone' ? 'milestone' : ($ticket['type'] === 'subtask' ? 'subtask' : 'task') }}"
     data-id="{{ $ticket['id'] }}"
     data-project="{{ $ticket['projectId'] }}"
     data-draggable="true"
     draggable="true"
     data-sort-index="{{ $ticket['sortIndex'] ?? 10 }}"
     data-event='{!! $ticketDataJson !!}'>

    @php
        $accordionId = 'task-children-'.$groupKey.$ticket['id'];
        $accordionState = $tpl->getToggleState($tpl->getToggleState("accordion_content-".$accordionId) === 'closed' ? 'closed' : 'open');
    @endphp

    <div
        class="tw:relative ticketBox {{ $ticket['type'] === 'milestone' ? 'milestone-box priority-border- ' : 'priority-border-'.$ticket['priority'] }} {{ $hasChildren ? 'has-children' : '' }}"
        data-val="{{ $ticket['id'] }}"
        data-event='{!! $ticketDataJson !!}'
        @if($ticket['type'] === 'milestone')
            style="background: var(--secondary-background) linear-gradient(135deg, {{ $ticket['tags'] }} 0%, var(--accent1) 100%); background-repeat: no-repeat; background-size: 100% 5px; background-position: bottom;"
        @endif
    >
        <div class="tw:absolute full-width-loader htmx-indicator-ticket-{{$ticket['id']}}">
            <div class="indeterminate"></div>
        </div>

        @if($hasChildren)
            <div id="accordion_toggle_{{$accordionId }}"
                 class="task-collapse-toggle accordion-toggle {{ $accordionState }}"
                 onclick="leantime.snippets.accordionToggle('{{ $accordionId}}');"
                 onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();this.click()}"
                 tabindex="0"
                 role="button"
                 aria-label="{{ __('label.toggle') }}"
            >
                <x-global::elements.icon name="{{ $accordionState == 'closed' ? 'chevron_right' : 'expand_more' }}" />
            </div>
        @endif

        @if($ticket['type'] == 'milestone')
            <div class="tw:flex tw:flex-row tw:items-center tw:gap-4">
                <div class="tw:flex-grow">
                    <small style="display:inline-block; ">{{ $ticket['projectName'] }}</small>
                    <h4><a href="#/tickets/editMilestone/{{ $ticket['id'] }}"
                           style="font-size:var(--font-size-l);">{{ $ticket['headline'] }}</a></h4>

                </div>
                <div class="tw:flex-grow">
                    <div hx-trigger="load"
                         hx-indicator=".htmx-indicator"
                         hx-target="this"
                         hx-swap="innerHTML"
                         hx-get="<?=BASE_URL ?>/hx/tickets/milestones/progress?milestoneId=<?=$ticket['id'] ?>&progressColor={{ trim($ticket['tags'], "#") }}">
                        <div class="htmx-indicator" role="status">
                                <?= $tpl->__("label.loading_milestone") ?>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="tw:flex tw:flex-row tw:items-center tw:gap-2">
                <div class="tw:shrink-0">
                    <x-globals::tickets.timer-button :parent-ticket-id="$ticket['id']" :on-the-clock="$onTheClock" />
                </div>

                <div class="tw:flex-1 tw:min-w-0 ticket-title ticket-title-wrapper">
                    <div class="title-text">
                        <small style="display:inline-block;">{{ $ticket['projectName'] }}</small> <br/>
                        <strong><a href="#/tickets/showTicket/{{ $ticket['id'] }}"
                                   class="ticket-headline-{{ $ticket['id'] }}">{{ $ticket['headline'] }}</a></strong>
                        &nbsp;<a href="javascript:void(0);" class="tw:hidden edit-button"
                                 data-tippy-content="{{ __('text.edit_task_headline') }}"><x-global::elements.icon name="edit" /></a>
                    </div>
                    <div class="tw:hidden edit-form">
                        <form class="tw:flex tw:flex-row tw:items-center tw:gap-2"
                              hx-post="{{ BASE_URL }}/hx/widgets/myToDos/updateTitle"
                              hx-target=".ticket-headline-{{ $ticket['id'] }}"
                              onsubmit="jQuery(this).closest('.edit-form').find('.cancel-edit-task').click();"
                        >
                            <input type="hidden" name="id" value="{{ $ticket['id'] }}"/>
                            <div>
                                <x-globals::forms.input :bare="true" type="text" name="headline" class="main-title-input"
                                       style="font-size:var(--base-font-size); margin-bottom:0px"
                                       value="{{ $ticket['headline'] }}" />
                            </div>
                            <div>
                                <x-globals::forms.button submit type="primary" name="edit" icon="check"></x-globals::forms.button>
                            </div>
                            <div>
                                <x-globals::forms.button link="javascript:void(0);" type="secondary" icon="close" class="cancel-edit-task" data-group="{{ $groupKey }}"></x-globals::forms.button>
                            </div>
                        </form>
                    </div>
                </div>

                @dispatchEvent('beforePlaceholder', ['ticket' => (object)$ticket])
                <div class="placeholder-container tw:flex tw:flex-row tw:items-center">
                    @dispatchEvent('placeholderContainer', ['ticket' => (object)$ticket])
                </div>

                @dispatchEvent('beforeDueDate', ['ticket' => (object)$ticket])
                <div class="due-date-container tw:shrink-0 tw:flex tw:flex-row tw:items-center tw:gap-1 due-date-wrapper">
                    <div class="date-picker-form-control">
                        <x-global::elements.icon name="business_center" class="infoIcon" data-tippy-content="{{ __("label.due") }}" />

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
                               hx-indicator=".htmx-indicator"/>
                        <button class="reset-button"
                                data-id="{{ $ticket['id'] }}"
                                id="reset-date-{{ $ticket['id'] }}"
                                hx-post="{{ BASE_URL }}/widgets/myToDos/updateDueDate"
                                hx-vals='{"id": "{{ $ticket['id'] }}", "date": ""}'
                                hx-indicator=".htmx-indicator">
                            <span class="sr-only">{{ __("language.resetDate") }}</span>
                            <x-global::elements.icon name="close" />
                        </button>
                    </div>
                    @dispatchEvent('afterDueDate', ['ticket' => (object)$ticket])
                </div>

                @dispatchEvent('beforeStatusUpdate')
                <div class="status-container tw:shrink-0 tw:flex tw:flex-row tw:items-center tw:gap-2">
                    <x-globals::actions.chip
                        content-role="status"
                        :parentId="$ticket['id']"
                        :selectedClass="$statusLabels[$ticket['projectId']][$ticket['status']]['class'] ?? 'label-default'"
                        :selectedKey="$ticket['status']"
                        :options="$statusLabels[$ticket['projectId']]"
                        :colorized="true"
                        align="end"
                        headerLabel="{{ __('dropdown.choose_status') }}"
                        hxPost="{{ BASE_URL }}/widgets/myToDos/updateStatus"
                        hxSwap="none"
                    />

                    <div class="scheduler">
                        @if( $ticket['editFrom'] != "0000-00-00 00:00:00" && $ticket['editFrom'] != "1969-12-31 00:00:00")
                            <x-global::elements.icon name="event_available" class="infoIcon" style="color:var(--accent2)"
                               data-tippy-content="{{ __('text.schedule_to_start_on') }} {{ format($ticket['editFrom'])->date() }}" />
                        @else
                            <x-global::elements.icon name="event_busy" class="infoIcon" data-tippy-content="{{ __('text.not_scheduled_drag_ai') }}" />
                        @endif
                    </div>

                    <x-globals::tickets.ticket-submenu :ticket="$ticket" :on-the-clock="$onTheClock" :allow-subtask-creation="true" />
                </div>

            </div>
        @endif

    </div>

    <!-- Subtask Form -->
    <div id="subtask-form-{{$ticket['id']}}" class="subtask-form ticketBox"
         style="display:none; margin:10px; margin-left:40px;">
        <form class="form-group"
              hx-post="{{ BASE_URL }}/widgets/myToDos/addSubtask?ticketId={{$ticket['id']}}"
              hx-target="#yourToDoContainer"
              hx-select="#yourToDoContainer"
              hx-swap="outerHTML"
              hx-indicator=".htmx-indicator">
            <input type="hidden" value="new" name="subtaskId"/>
            <input type="hidden" value="1" name="subtaskSave"/>
            <input type="hidden" value="{{ format($ticket['dateToFinish'])->date() }}" name="dateToFinish"/>
            <div class="tw:flex tw:flex-row tw:gap-2">
                <div class="tw:flex-grow">
                    <x-globals::forms.input :bare="true" type="text" name="headline" class="main-title-input"
                           style="font-size:var(--base-font-size)"
                           placeholder="{{ __('input.placeholders.what_are_you_working_on') }}" />
                </div>
                <div>
                    <input type="hidden" name="status" value="3"/>
                    <x-globals::forms.button submit type="primary">{{ __('buttons.save') }}</x-globals::forms.button>
                    <x-globals::forms.button link="javascript:void(0);" type="secondary" onclick="jQuery('#subtask-form-{{$ticket['id']}}').toggle();">{{ __('buttons.cancel') }}</x-globals::forms.button>
                </div>
            </div>
        </form>
    </div>
    <!-- End Subtask Form -->

    <div id="accordion_content-{{ $accordionId }}"
         style="{{ $accordionState =='closed' ? 'display:none;' : '' }}"
         class="sortable-list task-children {{ $tpl->getToggleState("user.".session('userdata.id').".taskCollapsed.".$ticket['id'], 'open') }}"
         data-container-type="{{ $ticket['type'] == 'milestone' ? 'milestone' : ($ticket['type'] == 'subtask' ? 'subtask' : 'task') }}">

        @foreach(($ticket['children'] ?? []) as $childTicket)
            @include('widgets::partials.todoItem', ['ticket' => $childTicket, 'statusLabels' => $statusLabels, 'onTheClock' => $onTheClock, 'tpl' => $tpl, 'level' => $level + 1, 'groupKey' => $groupKey])
        @endforeach

        @if($level == 0 && $ticket['type'] === "milestone")

            <!-- Subtask Form -->
            <div id="task-add-form-{{ $groupKey }}-{{$ticket['id']}}" class="subtask-form ticketBox"
                 style="display:none; margin:5px 0px;">
                <form class="form-group"
                      id="task-add-form-{{ $groupKey }}-{{$ticket['id']}}-form"
                      hx-post="{{ BASE_URL }}/widgets/myToDos/addTodo"
                      hx-target="#yourToDoContainer"
                      hx-select="#yourToDoContainer"
                      hx-swap="outerHTML"
                      hx-indicator=".htmx-indicator"
                      onsubmit="jQuery(this).find('.main-title-input').attr('readonly', true);"
                >
                    <input type="hidden" name="milestone"
                           value="{{ $ticket['type'] == "milestone" ? $ticket['id'] : '' }}"/>
                    <input type="hidden" name="status" value="3"/>
                    <input type="hidden" name="quickadd" value="true"/>
                    <input type="hidden" name="sortIndex"
                           value="{{ isset($ticket['children']) ? ((collect($ticket['children'])->last()['sortIndex'] ?? 10)+5) : 10 }}"/>
                    <input type="hidden" name="projectId" value="{{ $ticket['projectId'] }}"/>
                    <input type="hidden" name="priority"
                           value="{{ $groupBy === "priority" ? $groupKey : '' }}"/>
                    <input type="hidden" name="dateToFinish"
                           @if($groupKey === 'thisWeek')
                               value="{{ dtHelper()->userNow()->next('Friday')->formatDateForUser() }}"
                           @elseif($groupKey === 'overdue')
                               value="{{ dtHelper()->userNow()->yesterday()->formatDateForUser() }}"
                           @else
                               value=""
                        @endif
                    />
                    <div class="tw:flex tw:flex-row tw:gap-2">
                        <div class="tw:flex-grow">
                            <x-globals::forms.input :bare="true" type="text" name="headline" class="main-title-input"
                                   style="font-size:var(--base-font-size)"
                                   placeholder="{{ __('input.placeholders.what_are_you_working_on') }}" />
                        </div>
                        <div>
                            <input type="hidden" name="status" value="3"/>
                            <x-globals::forms.button submit type="primary">{{ __('buttons.save') }}</x-globals::forms.button>
                            <x-globals::forms.button link="javascript:void(0);" type="secondary" onclick="jQuery('#task-add-form-{{ $groupKey }}-{{$ticket['id']}}').toggle(); jQuery('#task-add-form-{{ $groupKey }}-{{$ticket['id']}}-handler').toggle();">{{ __('buttons.cancel') }}</x-globals::forms.button>
                        </div>
                    </div>
                </form>
            </div>
            <!-- End Subtask Form -->

            <a href="javascript:void(0);" id="task-add-form-{{ $groupKey }}-{{$ticket['id']}}-handler"
               onclick="jQuery(this).toggle(); jQuery('#task-add-form-{{ $groupKey }}-{{$ticket['id']}}').toggle(); "><x-global::elements.icon name="add_circle" /> {{ __('links.add_task') }}</a>
        @endif

    </div>
</div>
