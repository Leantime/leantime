@extends($layout)

@section('content')
    <?php

    $allTicketGroups = $tpl->get('allTickets');

    $todoTypeIcons = $tpl->get('ticketTypeIcons');

    $statusLabels = $tpl->get('allTicketStates');

    //All states >0 (<1 is archive)
    $numberofColumns = count($tpl->get('allTicketStates')) - 1;
    $size = floor(100 / $numberofColumns);

    ?>

    @include('tickets::includes.ticketHeader')

    <div class="maincontent">

        @include('tickets::includes.ticketBoardTabs')

        <div class="maincontentinner">

            <div class="row">
                <div class="col-md-4">
                    <?php
                    $tpl->dispatchTplEvent('filters.afterLefthandSectionOpen');
                    ?>

                    @include('tickets::includes.ticketNewBtn')
                    @include('tickets::includes.ticketFilter')

                    <?php
                    $tpl->dispatchTplEvent('filters.beforeLefthandSectionClose');
                    ?>
                </div>

                <div class="col-md-4 center">

                </div>
                <div class="col-md-4">
                    <div class="pull-right">

                        <?php $tpl->dispatchTplEvent('filters.afterRighthandSectionOpen'); ?>

                        <div id="tableButtons" style="display:inline-block"></div>

                        <?php $tpl->dispatchTplEvent('filters.beforeRighthandSectionClose'); ?>

                    </div>
                </div>

            </div>

            <div class="clearfix" style="margin-bottom: 20px;"></div>



            <?php if (isset($allTicketGroups['all'])) {
                $allTickets = $allTicketGroups['all']['items'];
            }
            ?>

            <x-global::elements.table extraClass="ticketTable display" style="width:100%">
                <x-slot:header>
                    @foreach ($allTicketGroups as $group)
                        @if ($group['label'] != 'all')
                            <h5 class="accordionTitle {{ $group['class'] }}" id="accordion_link_{{ $group['id'] }}">
                                <a href="javascript:void(0)" class="accordion-toggle" id="accordion_toggle_{{ $group['id'] }}"
                                    onclick="leantime.snippets.accordionToggle('{{ $group['id'] }}');">
                                    <i class="fa fa-angle-down"></i>{{ $group['label'] }} ({{ count($group['items']) }})
                                </a>
                            </h5>
                            <span>{{ $group['more-info'] }}</span>
                            <div class="simpleAccordionContainer" id="accordion_content-{{ $group['id'] }}">
                        @endif

                        @php
                            $allTickets = $group['items'];
                            $tpl->dispatchTplEvent('allTicketsTable.before', ['tickets' => $allTicketGroups]);
                        @endphp

                        <x-global::elements.table.header>
                            <x-global::elements.table.header-cell class="id-col">{{ $tpl->__('label.id') }}</x-global::elements.table.header-cell>
                            <x-global::elements.table.header-cell style="max-width: 350px;">{{ $tpl->__('label.title') }}</x-global::elements.table.header-cell>
                            <x-global::elements.table.header-cell class="status-col">{{ $tpl->__('label.todo_status') }}</x-global::elements.table.header-cell>
                            <x-global::elements.table.header-cell class="milestone-col">{{ $tpl->__('label.milestone') }}</x-global::elements.table.header-cell>
                            <x-global::elements.table.header-cell class="effort-col">{{ $tpl->__('label.effort') }}</x-global::elements.table.header-cell>
                            <x-global::elements.table.header-cell class="priority-col">{{ $tpl->__('label.priority') }}</x-global::elements.table.header-cell>
                            <x-global::elements.table.header-cell class="user-col">{{ $tpl->__('label.editor') }}</x-global::elements.table.header-cell>
                            <x-global::elements.table.header-cell class="sprint-col">{{ $tpl->__('label.sprint') }}</x-global::elements.table.header-cell>
                            <x-global::elements.table.header-cell class="tags-col">{{ $tpl->__('label.tags') }}</x-global::elements.table.header-cell>
                            <x-global::elements.table.header-cell class="duedate-col">{{ $tpl->__('label.due_date') }}</x-global::elements.table.header-cell>
                            <x-global::elements.table.header-cell class="planned-hours-col">{{ $tpl->__('label.planned_hours') }}</x-global::elements.table.header-cell>
                            <x-global::elements.table.header-cell class="remaining-hours-col">{{ $tpl->__('label.estimated_hours_remaining') }}</x-global::elements.table.header-cell>
                            <x-global::elements.table.header-cell class="booked-hours-col">{{ $tpl->__('label.booked_hours') }}</x-global::elements.table.header-cell>
                            <x-global::elements.table.header-cell class="no-sort"></x-global::elements.table.header-cell>
                        </x-global::elements.table.header>
                    @endforeach
                </x-slot:header>

                <x-slot:body>
                    @foreach ($allTicketGroups as $group)
                        @php $allTickets = $group['items']; @endphp

                        @foreach ($allTickets as $rowNum => $row)
                            <x-global::elements.table.row>
                                @php
                                    // Status handling
                                    if (isset($statusLabels[$row['status']])) {
                                        $class = $statusLabels[$row['status']]['class'];
                                        $name = $statusLabels[$row['status']]['name'];
                                        $sortKey = $statusLabels[$row['status']]['sortKey'];
                                        $selectedKey = $row['status'];
                                    } else {
                                        $class = 'label-important';
                                        $name = 'new';
                                        $sortKey = 0;
                                        $selectedKey = null;
                                    }

                                    // Milestone handling
                                    $milestoneHeadline = $row['milestoneid'] != '' && $row['milestoneid'] != 0
                                        ? $tpl->escape($row['milestoneHeadline'])
                                        : $tpl->__('label.no_milestone');
                                    $milestoneColor = $tpl->escape($row['milestoneColor']);
                                    $milestoneDropdownId = 'milestoneDropdownMenuLink' . $row['id'];
                                    $noMilestoneLabel = $tpl->__('label.no_milestone');

                                    // Effort handling
                                    $effortText = $row['storypoints'] != '' && $row['storypoints'] > 0
                                        ? $efforts['' . $row['storypoints']] ?? $row['storypoints']
                                        : $tpl->__('label.story_points_unkown');
                                    $dropdownId = 'effortDropdownMenuLink' . $row['id'];

                                    // Priority handling
                                    $priorityText = $row['priority'] != '' && $row['priority'] > 0
                                        ? ($priorities[$row['priority']] ??  $tpl->__('label.priority_unkown'))
                                        : $tpl->__('label.priority_unkown');
                                    $dropdownId = 'priorityDropdownMenuLink' . $row['id'];

                                    // User Text and Image Handling
                                    if ($row['editorFirstname'] != '') {
                                        $userText = "<span id='userImage" . $row['id'] . "'><img src='" . BASE_URL . '/api/users?profileImage=' . $row['editorId'] . "' width='25' style='vertical-align: middle; margin-right:5px;'/></span><span id='user" . $row['id'] . "'>" . $tpl->escape($row['editorFirstname']) . '</span>';
                                    } else {
                                        $userText = "<span id='userImage" . $row['id'] . "'><img src='" . BASE_URL . "/api/users?profileImage=false' width='25' style='vertical-align: middle; margin-right:5px;'/></span><span id='user" . $row['id'] . "'>" . $tpl->__('dropdown.not_assigned') . '</span>';
                                    }
                                    $dropdownId = 'userDropdownMenuLink' . $row['id'];

                                    // Sprint handling
                                    $sprintHeadline = $row['sprint'] != '' && $row['sprint'] != 0 && $row['sprint'] != -1
                                        ? $tpl->escape($row['sprintName'])
                                        : $tpl->__('links.no_list');
                                    $dropdownId = 'sprintDropdownMenuLink' . $row['id'];

                                    // Due date handling
                                    if ($row['dateToFinish'] == '0000-00-00 00:00:00' || $row['dateToFinish'] == '1969-12-31 00:00:00') {
                                        $date = $tpl->__('text.anytime');
                                    } else {
                                        $date = new DateTime($row['dateToFinish']);
                                        $date = $date->format($tpl->__('language.dateformat'));
                                    }
                                @endphp

                                <x-global::elements.table.cell data-order="{{ $row['id'] }}">
                                    #{{ $tpl->e($row['id']) }}
                                </x-global::elements.table.cell>

                                {{--repeat_output_issue data-order="{{ $tpl->e($row['headline']) }}" --}}
                                <x-global::elements.table.cell>
                                    @if ($row['dependingTicketId'] > 0)
                                        <small>
                                            <a
                                                href="#/tickets/showTicket/{{ $row['dependingTicketId'] }}"
                                            >
                                                {{ $tpl->escape($row['parentHeadline']) }}
                                            </a>
                                        </small>
                                        <br />
                                    @endif
                                    <a class='ticketModal'
                                        href="#/tickets/showTicket/{{ $tpl->e($row['id']) }}"
                                    >
                                        {{ $tpl->e($row['headline']) }}
                                    </a>
                                </x-global::elements.table.cell>

                                <x-global::elements.table.cell data-order="{{ $name }}">
                                    <x-global::forms._archive.dropdownPill
                                        class="ticketDropdown statusDropdown colorized show {{ $class }} f-left"
                                        id="statusDropdownMenuLink{{ $row['id'] }}"
                                        :selectedKey="$selectedKey"
                                        :options="$statusLabels">
                                        <x-slot name="buttonText">
                                            {{ $name }} <i class="fa fa-caret-down" aria-hidden="true"></i>
                                        </x-slot>

                                        <x-global::actions.dropdown.item class="header border">
                                            {{ __('dropdown.choose_status') }}
                                        </x-global::actions.dropdown.item>

                                        @foreach ($statusLabels as $key => $label)
                                            <x-global::actions.dropdown.item
                                                href="javascript:void(0);"
                                                :class="$label['class'] . ($selectedKey == $key ? ' selected' : '')"
                                                :data-label="$label['name']"
                                                :data-value="$row['id'] . '_' . $key . '_' . $label['class']"
                                                :id="'ticketStatusChange' . $row['id'] . $key">
                                                {{ $label['name'] }}
                                            </x-global::actions.dropdown.item>
                                        @endforeach
                                    </x-global::forms._archive.dropdownPill>
                                </x-global::elements.table.cell>

                                <x-global::elements.table.cell data-order="{{ $milestoneHeadline }}">
                                    <x-global::forms.chip
                                        class="ticketDropdown milestoneDropdown colorized milestone show f-left"
                                        :bgColor="'background-color:' . $milestoneColor"
                                        :labelText="$milestoneHeadline"
                                        type="milestone"
                                        :parentId="$row['id']"
                                        :quickaddOption="true"
                                        quickaddPostUrl="{{ BASE_URL }}/hx/tickets/Milestones"
                                    >
                                        <x-global::actions.dropdown.item variant="header">
                                            {{ __('dropdown.choose_milestone') }}
                                        </x-global::actions.dropdown.item>



                                        <x-global::actions.dropdown.item
                                            href="javascript:void(0);"
                                            :data-label="$noMilestoneLabel"
                                            :data-value="$row['id'] . '_0_#b0b0b0'"
                                            style="background-color:#b0b0b0"
                                            id="milestoneChange{{ $row['id'] }}0">
                                            {{ $noMilestoneLabel }}
                                        </x-global::actions.dropdown.item>

                                        @foreach ($tpl->get('milestones') as $milestone)
                                            <x-global::actions.dropdown.item
                                                href="javascript:void(0);"
                                                :data-label="$tpl->escape($milestone->headline)"
                                                :data-value="$row['id'] . '_' . $milestone->id . '_' . $tpl->escape($milestone->tags)"
                                                :id="'ticketMilestoneChange' . $row['id'] . $milestone->id"
                                                :style="'background-color:' . $tpl->escape($milestone->tags)">
                                                {{ $tpl->escape($milestone->headline) }}
                                            </x-global::actions.dropdown.item>
                                        @endforeach

                                    </x-global::forms.chip>
                                </x-global::elements.table.cell>

                                <x-global::elements.table.cell data-order="{{ $row['storypoints'] ? $efforts['' . $row['storypoints'] . ''] ?? '?' : $tpl->__('label.story_points_unkown') }}">
                                    <x-global::forms._archive.dropdownPill
                                        class="label-default effort f-left"
                                        id="{{ $dropdownId }}"
                                        :labelText="$effortText"
                                        type="effort"
                                        :parentId="$row['id']">
                                        <x-slot name="buttonText">
                                            {{ $effortText }} <i class="fa fa-caret-down" aria-hidden="true"></i>
                                        </x-slot>

                                        <x-global::actions.dropdown.item class="nav-header border">
                                            {{ __('dropdown.how_big_todo') }}
                                        </x-global::actions.dropdown.item>

                                        @foreach ($efforts as $effortKey => $effortValue)
                                            <x-global::actions.dropdown.item
                                                href="javascript:void(0);"
                                                :data-value="$row['id'] . '_' . $effortKey"
                                                :id="'ticketEffortChange' . $row['id'] . $effortKey">
                                                {{ $effortValue }}
                                            </x-global::actions.dropdown.item>
                                        @endforeach
                                    </x-global::forms._archive.dropdownPill>
                                </x-global::elements.table.cell>

                                <x-global::elements.table.cell data-order="{{ $priorityText }}">
                                    <x-global::forms._archive.dropdownPill
                                        class="ticketDropdown priorityDropdown show label-default priority priority-bg-{{ $row['priority'] }} f-left"
                                        id="{{ $dropdownId }}"
                                        :labelText="$priorityText"
                                        type="priority"
                                        :parentId="$row['id']"
                                        :selectedKey="$row['priority']">
                                        <x-slot name="buttonText">
                                            {{ $priorityText }} <i class="fa fa-caret-down" aria-hidden="true"></i>
                                        </x-slot>

                                        <x-global::actions.dropdown.item class="nav-header border">
                                            {{ __('dropdown.select_priority') }}
                                        </x-global::actions.dropdown.item>

                                        @foreach ($priorities as $priorityKey => $priorityValue)
                                            <x-global::actions.dropdown.item href="javascript:void(0);"
                                                class="priority-bg-{{ $priorityKey }}"
                                                :data-value="$row['id'] . '_' . $priorityKey"
                                                :id="'ticketPriorityChange' . $row['id'] . $priorityKey">
                                                {{ $priorityValue }}
                                            </x-global::actions.dropdown.item>
                                        @endforeach
                                    </x-global::forms._archive.dropdownPill>
                                </x-global::elements.table.cell>

                                <x-global::elements.table.cell data-order="{{ $row['editorFirstname'] != ''
                                    ? $tpl->escape($row['editorFirstname'])
                                    : $tpl->__('dropdown.not_assigned') }}">
                                    <x-global::forms._archive.dropdownPill
                                        class="ticketDropdown userDropdown noBg show f-left"
                                        id="{{ $dropdownId }}"
                                        :labelText="html_entity_decode($userText)"
                                        type="user"
                                        :parentId="$row['id']">
                                        <x-slot name="buttonText">
                                            {!! $userText !!} <i class="fa fa-caret-down" aria-hidden="true"></i>
                                        </x-slot>

                                        <x-global::actions.dropdown.item class="nav-header border">
                                            {{ __('dropdown.choose_user') }}
                                        </x-global::actions.dropdown.item>

                                        @foreach ($tpl->get('users') as $user)
                                            <x-global::actions.dropdown.item href="javascript:void(0);" :data-label="sprintf(
                                                $tpl->__('text.full_name'),
                                                $tpl->escape($user['firstname']),
                                                $tpl->escape($user['lastname']),
                                            )"
                                                :data-value="$row['id'] . '_' . $user['id'] . '_' . $user['profileId']" :id="'userStatusChange' . $row['id'] . $user['id']">
                                                <img src="{{ BASE_URL . '/api/users?profileImage=' . $user['id'] }}"
                                                    width="25" style="vertical-align: middle; margin-right:5px;" />
                                                {{ sprintf($tpl->__('text.full_name'), $tpl->escape($user['firstname']), $tpl->escape($user['lastname'])) }}
                                            </x-global::actions.dropdown.item>
                                        @endforeach
                                    </x-global::forms._archive.dropdownPill>
                                </x-global::elements.table.cell>

                                <x-global::elements.table.cell data-order="{{ $sprintHeadline }}">
                                    <x-global::forms._archive.dropdownPill
                                        class="ticketDropdown sprintDropdown show label-default sprint f-left"
                                        id="{{ $dropdownId }}"
                                        :labelText="$sprintHeadline"
                                        type="sprint"
                                        :parentId="$row['id']">
                                        <x-slot name="buttonText">
                                            {{ $sprintHeadline }} <i class="fa fa-caret-down" aria-hidden="true"></i>
                                        </x-slot>

                                        <x-global::actions.dropdown.item class="nav-header border">
                                            {{ __('dropdown.choose_list') }}
                                        </x-global::actions.dropdown.item>

                                        <x-global::actions.dropdown.item
                                            href="javascript:void(0);"
                                            :data-label="$tpl->__('label.not_assigned_to_list')"
                                            :data-value="$row['id'] . '_0'">
                                            {{ __('label.not_assigned_to_list') }}
                                        </x-global::actions.dropdown.item>

                                        @foreach ($tpl->get('sprints') as $sprint)
                                            <x-global::actions.dropdown.item
                                                href="javascript:void(0);"
                                                :data-label="$tpl->escape($sprint->name)"
                                                :data-value="$row['id'] . '_' . $sprint->id"
                                                :id="'ticketSprintChange' . $row['id'] . $sprint->id">
                                                {{ $tpl->escape($sprint->name) }}
                                            </x-global::actions.dropdown.item>
                                        @endforeach
                                    </x-global::forms._archive.dropdownPill>
                                </x-global::elements.table.cell>

                                <x-global::elements.table.cell data-order="{{ $row['tags'] }}">
                                    @if ($row['tags'] != '')
                                        @php $tagsArray = explode(',', $row['tags']); @endphp
                                        <div class='tagsinput readonly'>
                                            @foreach ($tagsArray as $tag)
                                                <span class='tag'><span>{{ $tpl->escape($tag) }}</span></span>
                                            @endforeach
                                        </div>
                                    @endif
                                </x-global::elements.table.cell>

                                <x-global::elements.table.cell data-order="{{ $row['dateToFinish'] }}">
                                    <x-global::forms.text-input
                                        type="text"
                                        :title="$tpl->__('label.due')"
                                        :value="$date"
                                        class="quickDueDates secretInput"
                                        :data-id="$row['id']"
                                        name="date" />
                                </x-global::elements.table.cell>

                                <x-global::elements.table.cell data-order="{{ $tpl->escape($row['planHours']) }}">
                                    <x-global::forms.text-input
                                        type="text"
                                        name="planHours"
                                        :value="$tpl->escape($row['planHours'])"
                                        class="small-input secretInput"
                                        :onchange="'leantime.ticketsController.updatePlannedHours(this, \'' . $row['id'] . '\'); jQuery(this).parent().attr(\'data-order\', jQuery(this).val());'" />
                                </x-global::elements.table.cell>

                                <x-global::elements.table.cell data-order="{{ $tpl->escape($row['hourRemaining']) }}">
                                    <x-global::forms.text-input
                                        type="text"
                                        name="remainingHours"
                                        :value="$tpl->escape($row['hourRemaining'])"
                                        class="small-input secretInput"
                                        :onchange="'leantime.ticketsController.updateRemainingHours(this, \'' . $row['id'] . '\');'"

                                        {{-- hx-post="/api/tickets"
                                        hx-trigger="change"
                                        hx-swap="innerHTML"
                                        :hx-vals="'{
                                            \"id\": \"' . $row['id'] . '\",
                                            \"hourRemaining\": this.value
                                        }'" --}}
                                    />
                                </x-global::elements.table.cell>

                                <x-global::elements.table.cell data-order="{{ $row['bookedHours'] === null || $row['bookedHours'] == '' ? '0' : $row['bookedHours'] }}">
                                    {{ $row['bookedHours'] === null || $row['bookedHours'] == '' ? '0' : $row['bookedHours'] }}
                                </x-global::elements.table.cell>

                                <x-global::elements.table.cell>
                                    @include('tickets::includes.ticketsubmenu', [
                                        'ticket' => $row,
                                        'onTheClock' => $onTheClock,
                                    ])
                                </x-global::elements.table.cell>

                            </x-global::elements.table.row>
                        @endforeach

                        @if ($group['label'] != 'all')
                            </div>
                        @endif
                    @endforeach
                </x-slot:body>

                <x-slot:footer>
                    <x-global::elements.table.footer>
                        <x-global::elements.table.cell colspan="9"></x-global::elements.table.cell>
                        <x-global::elements.table.cell></x-global::elements.table.cell>
                        <x-global::elements.table.cell></x-global::elements.table.cell>
                        <x-global::elements.table.cell></x-global::elements.table.cell>
                        <x-global::elements.table.cell></x-global::elements.table.cell>
                        <x-global::elements.table.cell></x-global::elements.table.cell>
                    </x-global::elements.table.footer>
                </x-slot:footer>
            </x-global::elements.table>

            {{-- button to add row --}}
            {{-- <p><button type="button" id="addRow">Add new row</button></p> --}}

        </div>
    </div>

    <script type="text/javascript">
        jQuery(document).ready(function() {
            <?php $tpl->dispatchTplEvent('scripts.afterOpen'); ?>


            <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
            leantime.ticketsController.initDueDateTimePickers();
            leantime.ticketsController.initUserDropdown();
            leantime.ticketsController.initMilestoneDropdown();
            leantime.ticketsController.initEffortDropdown();
            leantime.ticketsController.initPriorityDropdown();
            leantime.ticketsController.initSprintDropdown();
            leantime.ticketsController.initStatusDropdown();

            <?php } else { ?>
            leantime.authController.makeInputReadonly(".maincontentinner");
            <?php } ?>



            leantime.ticketsController.initTicketsTable("<?= $searchCriteria['groupBy'] ?>");

            <?php $tpl->dispatchTplEvent('scripts.beforeClose'); ?>

        });

        function toggleDetails(row) {
            const id = row.getAttribute('data-id');
            const detailsRow = document.querySelector(`tr[data-parent="${id}"]`);
            const expandIcon = row.querySelector('.expand-icon');

            if (detailsRow.classList.contains('hidden')) {
                detailsRow.classList.remove('hidden');
                expandIcon.textContent = '-';
            } else {
                detailsRow.classList.add('hidden');
                expandIcon.textContent = '+';
            }
        }
    </script>
@endsection
