@props([
    'ticket' => [],
    'statusKey' => '',
    'todoTypeIcons' => [],
    'priorities' => [],
    'efforts' => [],
    'milestones' => [],
    'users' => [],
    'onTheClock' => false,
])

@if ($ticket['status'] == $statusKey)
    <div class="ticketBox moveable container priority-border-{{ $ticket['priority'] }}" id="ticket_{{ $ticket['id'] }}">
        <div class="row">
            <div class="col-md-12">
                @include('tickets::includes.ticketsubmenu', [
                    'ticket' => $ticket,
                    'onTheClock' => $onTheClock,
                ])

                @if ($ticket['dependingTicketId'] > 0)
                    <small>
                        <a href="#/tickets/showTicket/{{ $ticket['dependingTicketId'] }}"
                            class="form-modal">{{ $ticket['parentHeadline'] }}</a>
                    </small>
                    //
                @endif
                @php
                    $type = strtolower($ticket['type']);

                @endphp
                @isset($todoTypeIcons[$type])
                    <small>
                        <i class="fa {!! $todoTypeIcons[$type] !!}"></i>
                        {!! __('label.' . $type) !!}
                    </small>
                @endisset
                <small>#{{ $ticket['id'] }}</small>

                <div class="kanbanCardContent">
                    <h4>
                        <a href="#/tickets/showTicket/{{ $ticket['id'] }}">{{ $ticket['headline'] }}</a>
                    </h4>
                    <div class="kanbanContent" style="margin-bottom: 20px">
                        {!! $ticket['description'] !!}
                    </div>
                </div>

                @if ($ticket['dateToFinish'] != '0000-00-00 00:00:00' && $ticket['dateToFinish'] != '1969-12-31 00:00:00')
                    <x-global::forms.text-input type="text" name="date" value="{!! format($ticket['dateToFinish'])->date() !!}"
                        title="{{ __('label.due') }}" class="duedates secretInput" style="margin-left: 0px;"
                        data-id="{{ $ticket['id'] }}" leadingVisual="{!! __('label.due_icon') !!}" />
                @endif
            </div>
        </div>

        <div class="clearfix" style="padding-bottom: 8px;"></div>

        <div class="timerContainer" id="timerContainer-{{ $ticket['id'] }}">
            <x-tickets::chips.milestone-select :ticket="$ticket" :milestones="$milestones" :label="false"/>

            @if ($ticket['storypoints'] != '' && $ticket['storypoints'] > 0)
                <x-tickets::chips.effort-select :ticket="$ticket" :efforts="$efforts" :label="false"/>
            @endif

            <x-tickets::chips.priority-select :ticket="$ticket" :priorities="$priorities" :label="false"/>

            <div class="dropdown ticketDropdown userDropdown noBg show right lastDropdown dropRight">
                @php
                    // Determine the label text dynamically
                    $userLabelText = '<span class="text">';
                    if ($ticket['editorFirstname'] != '') {
                        $userLabelText .=
                            "<span id='userImage" .
                            $ticket['id'] .
                            "'><img src='" .
                            BASE_URL .
                            '/api/users?profileImage=' .
                            $ticket['editorId'] .
                            "' width='25' style='vertical-align: middle;'/></span>";
                    } else {
                        $userLabelText .=
                            "<span id='userImage" .
                            $ticket['id'] .
                            "'><img src='" .
                            BASE_URL .
                            "/api/users?profileImage=false' width='25' style='vertical-align: middle;'/></span>";
                    }
                    $userLabelText .= '</span>';
                @endphp

                <!-- Dropdown Component -->
                <x-global::actions.dropdown :label-text="$userLabelText" contentRole="link" position="bottom" align="start">
                    <!-- Dropdown Items -->
                    <x-slot:menu>
                        <x-global::actions.dropdown.item variant="header">
                            {{ $tpl->__('dropdown.choose_user') }}
                        </x-global::actions.dropdown.item>

                        @if (is_array($tpl->get('users')))
                            @foreach ($tpl->get('users') as $user)
                                <x-global::actions.dropdown.item href="javascript:void(0);"
                                    data-label="{{ sprintf($tpl->__('text.full_name'), $tpl->escape($user['firstname']), $tpl->escape($user['lastname'])) }}"
                                    data-value="{{ $ticket['id'] . '_' . $user['id'] . '_' . $user['profileId'] }}"
                                    id="userStatusChange{{ $ticket['id'] . $user['id'] }}">
                                    <img src="{{ BASE_URL }}/api/users?profileImage={{ $user['id'] }}"
                                        width="25" style="vertical-align: middle; margin-right:5px;" />
                                    {{ sprintf($tpl->__('text.full_name'), $tpl->escape($user['firstname']), $tpl->escape($user['lastname'])) }}
                                </x-global::actions.dropdown.item>
                            @endforeach
                        @endif
                    </x-slot:menu>
                </x-global::actions.dropdown>

            </div>
        </div>

        <div class="clearfix"></div>

        @if ($ticket['commentCount'] > 0 || $ticket['subtaskCount'] > 0 || $ticket['tags'] != '')
            <div class="row">
                <div class="col-md-12 border-top" style="white-space: nowrap;">
                    @if ($ticket['commentCount'] > 0)
                        <a href="#/tickets/showTicket/{{ $ticket['id'] }}">
                            <span class="fa-regular fa-comments"></span>
                            {{ $ticket['commentCount'] }}
                        </a>&nbsp;
                    @endif

                    @if ($ticket['subtaskCount'] > 0)
                        <a id="subtaskLink_{{ $ticket['id'] }}" href="#/tickets/showTicket/{{ $ticket['id'] }}"
                            class="subtaskLineLink">
                            <span class="fa fa-diagram-successor"></span>
                            {{ $ticket['subtaskCount'] }}
                        </a>&nbsp;
                    @endif

                    @if ($ticket['tags'] != '')
                        @if ($ticket['tags'])
                            @php
                                $tagsArray = explode(',', $ticket['tags']);
                            @endphp
                            <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">
                                <i class="fa fa-tags" aria-hidden="true"></i> {{ count($tagsArray) }}
                            </a>
                            <ul class="dropdown-menu">
                                <li style="padding:10px">
                                    <div class='tagsinput readonly'>
                                        @foreach ($tagsArray as $tag)
                                            <span class='tag'><span>{{ $tag }}</span></span>
                                        @endforeach
                                    </div>
                                </li>
                            </ul>
                        @endif
                    @endif
                </div>
            </div>
        @endif
    </div>
@endif
