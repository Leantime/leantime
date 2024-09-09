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

    <?php $tpl->displaySubmodule('tickets-ticketHeader'); ?>

    <div class="maincontent">

        <?php $tpl->displaySubmodule('tickets-ticketBoardTabs'); ?>

        <div class="maincontentinner">

            <div class="row">
                <div class="col-md-4">
                    <?php
                    $tpl->dispatchTplEvent('filters.afterLefthandSectionOpen');
                    
                    $tpl->displaySubmodule('tickets-ticketNewBtn');
                    $tpl->displaySubmodule('tickets-ticketFilter');
                    
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

            <?php foreach ($allTicketGroups as $group) {?>
            <?php if ($group['label'] != 'all') { ?>
            <h5 class="accordionTitle <?= $group['class'] ?>" id="accordion_link_<?= $group['id'] ?>">
                <a href="javascript:void(0)" class="accordion-toggle" id="accordion_toggle_<?= $group['id'] ?>"
                    onclick="leantime.snippets.accordionToggle('<?= $group['id'] ?>');">
                    <i class="fa fa-angle-down"></i><?= $group['label'] ?>(<?= count($group['items']) ?>)
                </a>
            </h5>
            <span><?= $group['more-info'] ?></span>
            <div class="simpleAccordionContainer" id="accordion_content-<?= $group['id'] ?>">
                <?php } ?>

                <?php $allTickets = $group['items']; ?>

                <?php $tpl->dispatchTplEvent('allTicketsTable.before', ['tickets' => $allTicketGroups]); ?>
                <table class="table table-bordered display ticketTable " style="width:100%">
                    <colgroup>
                        <col class="con1">
                        <col class="con0" style="max-width:200px;">
                        <col class="con1">
                        <col class="con0">
                        <col class="con1">
                        <col class="con0">
                        <col class="con1">
                        <col class="con0">
                        <col class="con1">
                        <col class="con0">
                        <col class="con1">
                        <col class="con0">
                        <col class="con1">
                        <col class="con0">
                    </colgroup>
                    <?php $tpl->dispatchTplEvent('allTicketsTable.beforeHead', ['tickets' => $allTickets]); ?>
                    <thead>
                        <?php $tpl->dispatchTplEvent('allTicketsTable.beforeHeadRow', ['tickets' => $allTickets]); ?>
                        <tr>
                            <th class="id-col"><?= $tpl->__('label.id') ?></th>
                            <th style="max-width: 350px;"><?= $tpl->__('label.title') ?></th>
                            <th class="status-col"><?= $tpl->__('label.todo_status') ?></th>
                            <th class="milestone-col"><?= $tpl->__('label.milestone') ?></th>
                            <th class="effort-col"><?= $tpl->__('label.effort') ?></th>
                            <th class="priority-col"><?= $tpl->__('label.priority') ?></th>
                            <th class="user-col"><?= $tpl->__('label.editor') ?>.</th>
                            <th class="sprint-col"><?= $tpl->__('label.sprint') ?></th>
                            <th class="tags-col"><?= $tpl->__('label.tags') ?></th>
                            <th class="duedate-col"><?= $tpl->__('label.due_date') ?></th>
                            <th class="planned-hours-col"><?= $tpl->__('label.planned_hours') ?></th>
                            <th class="remaining-hours-col"><?= $tpl->__('label.estimated_hours_remaining') ?></th>
                            <th class="booked-hours-col"><?= $tpl->__('label.booked_hours') ?></th>
                            <th class="no-sort"></th>
                        </tr>
                        <?php $tpl->dispatchTplEvent('allTicketsTable.afterHeadRow', ['tickets' => $allTickets]); ?>
                    </thead>
                    <?php $tpl->dispatchTplEvent('allTicketsTable.afterHead', ['tickets' => $allTickets]); ?>
                    <tbody>
                        <?php $tpl->dispatchTplEvent('allTicketsTable.beforeFirstRow', ['tickets' => $allTickets]); ?>
                        <?php foreach ($allTickets as $rowNum => $row) {?>
                        <tr style="height:1px;">
                            <?php $tpl->dispatchTplEvent('allTicketsTable.afterRowStart', ['rowNum' => $rowNum, 'tickets' => $allTickets]); ?>
                            <td data-order="<?= $tpl->e($row['id']) ?>">
                                #<?= $tpl->e($row['id']) ?>
                            </td>

                            <td data-order="<?= $tpl->e($row['headline']) ?>">
                                <?php if ($row['dependingTicketId'] > 0) { ?>
                                <small><a
                                        href="#/tickets/showTicket/<?= $row['dependingTicketId'] ?>"><?= $tpl->escape($row['parentHeadline']) ?></a></small>
                                //<br />
                                <?php } ?>
                                <a class='ticketModal'
                                    href="#/tickets/showTicket/<?= $tpl->e($row['id']) ?>"><?= $tpl->e($row['headline']) ?></a>
                            </td>



                            <?php
                            
                            if (isset($statusLabels[$row['status']])) {
                                $class = $statusLabels[$row['status']]['class'];
                                $name = $statusLabels[$row['status']]['name'];
                                $sortKey = $statusLabels[$row['status']]['sortKey'];
                            } else {
                                $class = 'label-important';
                                $name = 'new';
                                $sortKey = 0;
                            }
                            
                            ?>
                            <td data-order="<?= $name ?>">
                                <div class="dropdown ticketDropdown statusDropdown colorized show ">
                                    <x-global::content.context-menu
                                        label-text="<span class='text'>{{ $name }}</span> &nbsp;<i class='fa fa-caret-down' aria-hidden='true'></i>"
                                        contentRole="link" position="bottom" align="start"
                                        class="status {{ $class }} f-left">

                                        <!-- Menu Header -->
                                        <li class="nav-header border">{{ __('dropdown.choose_status') }}</li>

                                        <!-- Menu Items -->
                                        @foreach ($statusLabels as $key => $label)
                                            <x-global::actions.dropdown.item href="javascript:void(0);"
                                                class="{{ $label['class'] }}" data-label="{{ $label['name'] }}"
                                                data-value="{{ $row['id'] . '_' . $key . '_' . $label['class'] }}"
                                                id="ticketStatusChange{{ $row['id'] . $key }}">
                                                {{ $label['name'] }}
                                            </x-global::actions.dropdown.item>
                                        @endforeach

                                    </x-global::content.context-menu>

                                </div>
                            </td>



                            <?php
                            if ($row['milestoneid'] != '' && $row['milestoneid'] != 0) {
                                $milestoneHeadline = $tpl->escape($row['milestoneHeadline']);
                            } else {
                                $milestoneHeadline = $tpl->__('label.no_milestone');
                            } ?>

                            <td data-order="<?= $milestoneHeadline ?>">
                                <x-global::content.context-menu
                                    label-text="<span class='text'>{{ $milestoneHeadline }}</span> &nbsp;<i class='fa fa-caret-down' aria-hidden='true'></i>"
                                    contentRole="link" position="bottom" align="start"
                                    class="label-default milestone f-left"
                                    style="background-color: {{ $row['milestoneColor'] }}">

                                    <!-- Menu Header -->
                                    <li class="nav-header border">{{ __('dropdown.choose_milestone') }}</li>

                                    <!-- No Milestone Option -->
                                    <x-global::actions.dropdown.item href="javascript:void(0);"
                                        style="background-color: #b0b0b0;" data-label="{{ __('label.no_milestone') }}"
                                        data-value="{{ $row['id'] . '_0_#b0b0b0' }}">
                                        {{ __('label.no_milestone') }}
                                    </x-global::actions.dropdown.item>

                                    <!-- Dynamic Milestones -->
                                    @foreach ($milestones as $milestone)
                                        <x-global::actions.dropdown.item href="javascript:void(0);"
                                            style="background-color: {{ $milestone->tags }}"
                                            data-label="{{ $milestone->headline }}"
                                            data-value="{{ $row['id'] . '_' . $milestone->id . '_' . $milestone->tags }}"
                                            id="ticketMilestoneChange{{ $row['id'] . $milestone->id }}">
                                            {{ $milestone->headline }}
                                        </x-global::actions.dropdown.item>
                                    @endforeach

                                </x-global::content.context-menu>

                            </td>
                            <td
                                data-order="<?= $row['storypoints'] ? $efforts['' . $row['storypoints'] . ''] ?? '?' : $tpl->__('label.story_points_unkown') ?>">
                                @php
                                    // Determine the label text based on conditions
                                    $labelText =
                                        $row['storypoints'] != '' && $row['storypoints'] > 0
                                            ? $efforts[$row['storypoints']] ?? $row['storypoints']
                                            : __('label.story_points_unkown');
                                @endphp

                                <!-- Use the determined label text in the dropdown component -->
                                <x-global::content.context-menu :label-text="htmlspecialchars($labelText) .
                                    '&nbsp;<i class=\'fa fa-caret-down\' aria-hidden=\'true\'></i>'" contentRole="link" position="bottom"
                                    align="start" class="label-default effort f-left">

                                    <!-- Menu Header -->
                                    <li class="nav-header border">{{ __('dropdown.how_big_todo') }}</li>

                                    <!-- Dynamic Effort Options -->
                                    @foreach ($efforts as $effortKey => $effortValue)
                                        <x-global::actions.dropdown.item href="javascript:void(0);"
                                            data-value="{{ $row['id'] . '_' . $effortKey }}"
                                            id="ticketEffortChange{{ $row['id'] . $effortKey }}">
                                            {{ $effortValue }}
                                        </x-global::actions.dropdown.item>
                                    @endforeach

                                </x-global::content.context-menu>

                            </td>

                            <td data-order="<?php
                            if ($row['priority'] != '' && $row['priority'] > 0) {
                                echo $priorities[$row['priority']];
                            } else {
                                echo $tpl->__('label.priority_unkown');
                            } ?>">
                                @php
                                    // Determine the label text based on the priority value
                                    $labelText =
                                        $row['priority'] != '' && $row['priority'] > 0
                                            ? $priorities[$row['priority']]
                                            : __('label.priority_unkown');

                                    // Generate the class for the priority background
                                    $priorityClass = 'priority-bg-' . $row['priority'];
                                @endphp

                                <!-- Use the determined label text and class in the dropdown component -->
                                <x-global::content.context-menu :label-text="htmlspecialchars($labelText) .
                                    '&nbsp;<i class=\'fa fa-caret-down\' aria-hidden=\'true\'></i>'" contentRole="link" position="bottom"
                                    align="start" class="label-default priority {{ $priorityClass }} f-left">

                                    <!-- Menu Header -->
                                    <li class="nav-header border">{{ __('dropdown.select_priority') }}</li>

                                    <!-- Dynamic Priority Options -->
                                    @foreach ($priorities as $priorityKey => $priorityValue)
                                        <x-global::actions.dropdown.item href="javascript:void(0);"
                                            class="priority-bg-{{ $priorityKey }}"
                                            data-value="{{ $row['id'] . '_' . $priorityKey }}"
                                            id="ticketPriorityChange{{ $row['id'] . $priorityKey }}">
                                            {{ $priorityValue }}
                                        </x-global::actions.dropdown.item>
                                    @endforeach

                                </x-global::content.context-menu>

                            </td>
                            <td
                                data-order="<?= $row['editorFirstname'] != '' ? $tpl->escape($row['editorFirstname']) : $tpl->__('dropdown.not_assigned') ?>">
                                <div class="dropdown ticketDropdown userDropdown noBg show f-left">
                                    @php
                                        // Determine the label text with user information
                                        $labelText =
                                            $row['editorFirstname'] != ''
                                                ? "<span id='userImage{$row['id']}'><img src='" .
                                                    BASE_URL .
                                                    '/api/users?profileImage=' .
                                                    $row['editorId'] .
                                                    "' width='25' style='vertical-align: middle; margin-right:5px;'/></span><span id='user{$row['id']}'>" .
                                                    $tpl->escape($row['editorFirstname']) .
                                                    '</span>'
                                                : "<span id='userImage{$row['id']}'><img src='" .
                                                    BASE_URL .
                                                    "/api/users?profileImage=false' width='25' style='vertical-align: middle; margin-right:5px;'/></span><span id='user{$row['id']}'>" .
                                                    $tpl->__('dropdown.not_assigned') .
                                                    '</span>';
                                    @endphp

                                    <!-- Use the determined label text in the dropdown component -->
                                    <x-global::content.context-menu :label-text="htmlspecialchars($labelText) .
                                        '&nbsp;<i class=\'fa fa-caret-down\' aria-hidden=\'true\'></i>'" contentRole="link" position="bottom"
                                        align="start">

                                        <!-- Menu Header -->
                                        <li class="nav-header border">{{ __('dropdown.choose_user') }}</li>

                                        <!-- Dynamic User Options -->
                                        @foreach ($tpl->get('users') as $user)
                                            <x-global::actions.dropdown.item href="javascript:void(0);" :data-label="sprintf(
                                                __('text.full_name'),
                                                $tpl->escape($user['firstname']),
                                                $tpl->escape($user['lastname']),
                                            )"
                                                :data-value="$row['id'] . '_' . $user['id'] . '_' . $user['profileId']" id="userStatusChange{{ $row['id'] . $user['id'] }}">
                                                <img src="{{ BASE_URL }}/api/users?profileImage={{ $user['id'] }}"
                                                    width='25' style='vertical-align: middle; margin-right:5px;' />
                                                {{ sprintf(__('text.full_name'), $tpl->escape($user['firstname']), $tpl->escape($user['lastname'])) }}
                                            </x-global::actions.dropdown.item>
                                        @endforeach

                                    </x-global::content.context-menu>

                                </div>
                            </td>
                            <?php
                            
                            if ($row['sprint'] != '' && $row['sprint'] != 0 && $row['sprint'] != -1) {
                                $sprintHeadline = $tpl->escape($row['sprintName']);
                            } else {
                                $sprintHeadline = $tpl->__('links.no_list');
                            } ?>

                            <td data-order="<?= $sprintHeadline ?>">

                                @php
                                    // Determine the label text for the dropdown
                                    $labelText =
                                        "<span class='text'>" .
                                        $sprintHeadline .
                                        "</span> <i class='fa fa-caret-down' aria-hidden='true'></i>";
                                @endphp

                                <!-- Use the determined label text in the dropdown component -->
                                <x-global::content.context-menu :label-text="$labelText" contentRole="link" position="bottom"
                                    align="start">

                                    <!-- Menu Header -->
                                    <li class="nav-header border">{{ __('dropdown.choose_list') }}</li>

                                    <!-- Not Assigned Option -->
                                    <x-global::actions.dropdown.item href="javascript:void(0);" :data-label="__('label.not_assigned_to_list')"
                                        :data-value="$row['id'] . '_0'">
                                        {{ __('label.not_assigned_to_list') }}
                                    </x-global::actions.dropdown.item>

                                    <!-- Dynamic Sprint Options -->
                                    @foreach ($tpl->get('sprints') as $sprint)
                                        <x-global::actions.dropdown.item href="javascript:void(0);" :data-label="$tpl->escape($sprint->name)"
                                            :data-value="$row['id'] . '_' . $sprint->id" id="ticketSprintChange{{ $row['id'] . $sprint->id }}">
                                            {{ $tpl->escape($sprint->name) }}
                                        </x-global::actions.dropdown.item>
                                    @endforeach

                                </x-global::content.context-menu>

                            </td>

                            <td data-order="<?= $row['tags'] ?>">
                                <?php if ($row['tags'] != '') {?>
                                <?php $tagsArray = explode(',', $row['tags']); ?>
                                <div class='tagsinput readonly'>
                                    <?php
                                    
                                    foreach ($tagsArray as $tag) {
                                        echo "<span class='tag'><span>" . $tpl->escape($tag) . '</span></span>';
                                    }
                                    
                                    ?>
                                </div>
                                <?php } ?>
                            </td>

                            <?php
                            if ($row['dateToFinish'] == '0000-00-00 00:00:00' || $row['dateToFinish'] == '1969-12-31 00:00:00') {
                                $date = $tpl->__('text.anytime');
                            } else {
                                $date = new DateTime($row['dateToFinish']);
                                $date = $date->format($tpl->__('language.dateformat'));
                            }
                            ?>
                            <td data-order="<?= $row['dateToFinish'] ?>">
                                <input type="text" title="<?php echo $tpl->__('label.due'); ?>" value="<?php echo $date; ?>"
                                    class="quickDueDates secretInput" data-id="<?php echo $row['id']; ?>" name="date" />
                            </td>
                            <td data-order="<?= $tpl->e($row['planHours']) ?>">
                                <input type="text" value="<?= $tpl->e($row['planHours']) ?>" name="planHours"
                                    class="small-input secretInput"
                                    onchange="leantime.ticketsController.updatePlannedHours(this, '<?= $row['id'] ?>'); jQuery(this).parent().attr('data-order',jQuery(this).val());" />
                            </td>
                            <td data-order="<?= $tpl->e($row['hourRemaining']) ?>">
                                <input type="text" value="<?= $tpl->e($row['hourRemaining']) ?>" name="remainingHours"
                                    class="small-input secretInput"
                                    onchange="leantime.ticketsController.updateRemainingHours(this, '<?= $row['id'] ?>');" />
                            </td>

                            <td data-order="<?php if ($row['bookedHours'] === null || $row['bookedHours'] == '') {
                                echo '0';
                            } else {
                                echo $row['bookedHours'];
                            } ?>">

                                <?php if ($row['bookedHours'] === null || $row['bookedHours'] == '') {
                                    echo '0';
                                } else {
                                    echo $row['bookedHours'];
                                } ?>
                            </td>
                            <td>
                                <?php echo app('blade.compiler')::render(
                                    '@include("tickets::partials.ticketsubmenu", [
                                                                                                                                                                                                                                                                                        "ticket" => $ticket,
                                                                                                                                                                                                                                                                                        "onTheClock" => $onTheClock
                                                                                                                                                                                                                                                                                    ])',
                                    ['ticket' => $row, 'onTheClock' => $tpl->get('onTheClock')],
                                ); ?>


                            </td>
                            <?php $tpl->dispatchTplEvent('allTicketsTable.beforeRowEnd', ['tickets' => $allTickets, 'rowNum' => $rowNum]); ?>
                        </tr>
                        <?php } ?>
                        <?php $tpl->dispatchTplEvent('allTicketsTable.afterLastRow', ['tickets' => $allTickets]); ?>
                    </tbody>
                    <?php $tpl->dispatchTplEvent('allTicketsTable.afterBody', ['tickets' => $allTickets]); ?>
                    <tfoot align="right">
                        <tr>
                            <td colspan="9"></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tfoot>

                </table>
                <?php $tpl->dispatchTplEvent('allTicketsTable.afterClose', ['tickets' => $allTickets]); ?>

                <?php if ($group['label'] != 'all') { ?>
            </div>
            <?php } ?>
            <?php } ?>




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
    </script>
@endsection
