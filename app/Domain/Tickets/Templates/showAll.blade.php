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
                                $selectedKey = $row['status']; // Store the selected key
                            } else {
                                $class = 'label-important';
                                $name = 'new';
                                $sortKey = 0;
                                $selectedKey = null; // No selected key
                            }
                            ?>
                            <td data-order="{{ $name }}">
                                <x-global::forms._archive.dropdownPill
                                    class="ticketDropdown statusDropdown colorized show {{ $class }} f-left"
                                    id="statusDropdownMenuLink{{ $row['id'] }}" :selectedKey="$selectedKey" :options="$statusLabels">
                                    <x-slot name="buttonText">
                                        {{ $name }} <i class="fa fa-caret-down" aria-hidden="true"></i>
                                    </x-slot>

                                    <x-global::actions.dropdown.item class="nav-header border">
                                        {{ __('dropdown.choose_status') }}
                                    </x-global::actions.dropdown.item>

                                    @foreach ($statusLabels as $key => $label)
                                        <x-global::actions.dropdown.item href="javascript:void(0);" :class="$label['class'] . ($selectedKey == $key ? ' selected' : '')"
                                            :data-label="$label['name']" :data-value="$row['id'] . '_' . $key . '_' . $label['class']" :id="'ticketStatusChange' . $row['id'] . $key">
                                            {{ $label['name'] }}
                                        </x-global::actions.dropdown.item>
                                    @endforeach
                                </x-global::forms._archive.dropdownPill>
                            </td>




                            <?php
                            // Determine the milestone headline based on existing logic
                            $milestoneHeadline = $row['milestoneid'] != '' && $row['milestoneid'] != 0 ? $tpl->escape($row['milestoneHeadline']) : $tpl->__('label.no_milestone');
                            
                            $milestoneColor = $tpl->escape($row['milestoneColor']);
                            $milestoneDropdownId = 'milestoneDropdownMenuLink' . $row['id'];
                            $noMilestoneLabel = $tpl->__('label.no_milestone');
                            ?>

                            <td data-order="{{ $milestoneHeadline }}">
                                <x-global::forms._archive.dropdownPill
                                    class="ticketDropdown milestoneDropdown colorized show label-default milestone f-left"
                                    id="{{ $milestoneDropdownId }}" :style="'background-color:' . $milestoneColor" :labelText="$milestoneHeadline" type="milestone"
                                    :parentId="$row['id']">
                                    <x-slot name="buttonText">
                                        {{ $milestoneHeadline }} <i class="fa fa-caret-down" aria-hidden="true"></i>
                                    </x-slot>

                                    <x-global::actions.dropdown.item class="nav-header border">
                                        {{ __('dropdown.choose_milestone') }}
                                    </x-global::actions.dropdown.item>

                                    <x-global::actions.dropdown.item href="javascript:void(0);" :data-label="$noMilestoneLabel"
                                        :data-value="$row['id'] . '_0_#b0b0b0'" style="background-color:#b0b0b0"
                                        id="milestoneChange{{ $row['id'] }}0">
                                        {{ $noMilestoneLabel }}
                                    </x-global::actions.dropdown.item>

                                    @foreach ($tpl->get('milestones') as $milestone)
                                        <x-global::actions.dropdown.item href="javascript:void(0);" :data-label="$tpl->escape($milestone->headline)"
                                            :data-value="$row['id'] .
                                                '_' .
                                                $milestone->id .
                                                '_' .
                                                $tpl->escape($milestone->tags)" :id="'ticketMilestoneChange' . $row['id'] . $milestone->id" :style="'background-color:' . $tpl->escape($milestone->tags)">
                                            {{ $tpl->escape($milestone->headline) }}
                                        </x-global::actions.dropdown.item>
                                    @endforeach
                                </x-global::forms._archive.dropdownPill>
                            </td>

                            <td
                                data-order="<?= $row['storypoints'] ? $efforts['' . $row['storypoints'] . ''] ?? '?' : $tpl->__('label.story_points_unkown') ?>">
                                <?php
                                // Determine the effort text to display based on the existing PHP logic
                                $effortText = $row['storypoints'] != '' && $row['storypoints'] > 0 ? $efforts['' . $row['storypoints']] ?? $row['storypoints'] : $tpl->__('label.story_points_unkown');
                                
                                $dropdownId = 'effortDropdownMenuLink' . $row['id'];
                                ?>

                                <x-global::forms._archive.dropdownPill class="label-default effort f-left" id="{{ $dropdownId }}"
                                    :labelText="$effortText" type="effort" :parentId="$row['id']">
                                    <x-slot name="buttonText">
                                        {{ $effortText }} <i class="fa fa-caret-down" aria-hidden="true"></i>
                                    </x-slot>

                                    <x-global::actions.dropdown.item class="nav-header border">
                                        {{ __('dropdown.how_big_todo') }}
                                    </x-global::actions.dropdown.item>

                                    @foreach ($efforts as $effortKey => $effortValue)
                                        <x-global::actions.dropdown.item href="javascript:void(0);" :data-value="$row['id'] . '_' . $effortKey"
                                            :id="'ticketEffortChange' . $row['id'] . $effortKey">
                                            {{ $effortValue }}
                                        </x-global::actions.dropdown.item>
                                    @endforeach
                                </x-global::forms._archive.dropdownPill>

                            </td>

                            <?php
                            // Determine the priority text to display based on the existing PHP logic
                            $priorityText = $row['priority'] != '' && $row['priority'] > 0 ? $priorities[$row['priority']] : $tpl->__('label.priority_unkown');
                            
                            $dropdownId = 'priorityDropdownMenuLink' . $row['id'];
                            ?>

                            <td data-order="{{ $priorityText }}">
                                <x-global::forms._archive.dropdownPill
                                    class="ticketDropdown priorityDropdown show label-default priority priority-bg-{{ $row['priority'] }} f-left"
                                    id="{{ $dropdownId }}" :labelText="$priorityText" type="priority" :parentId="$row['id']"
                                    :selectedKey="$row['priority']">
                                    <x-slot name="buttonText">
                                        {{ $priorityText }} <i class="fa fa-caret-down" aria-hidden="true"></i>
                                    </x-slot>

                                    <x-global::actions.dropdown.item class="nav-header border">
                                        {{ __('dropdown.select_priority') }}
                                    </x-global::actions.dropdown.item>

                                    @foreach ($priorities as $priorityKey => $priorityValue)
                                        <x-global::actions.dropdown.item href="javascript:void(0);"
                                            class="priority-bg-{{ $priorityKey }}" :data-value="$row['id'] . '_' . $priorityKey" :id="'ticketPriorityChange' . $row['id'] . $priorityKey">
                                            {{ $priorityValue }}
                                        </x-global::actions.dropdown.item>
                                    @endforeach
                                </x-global::forms._archive.dropdownPill>
                            </td>

                            <?php
                            // Determine the user text and image to display
                            if ($row['editorFirstname'] != '') {
                                $userText = "<span id='userImage" . $row['id'] . "'><img src='" . BASE_URL . '/api/users?profileImage=' . $row['editorId'] . "' width='25' style='vertical-align: middle; margin-right:5px;'/></span><span id='user" . $row['id'] . "'>" . $tpl->escape($row['editorFirstname']) . '</span>';
                            } else {
                                $userText = "<span id='userImage" . $row['id'] . "'><img src='" . BASE_URL . "/api/users?profileImage=false' width='25' style='vertical-align: middle; margin-right:5px;'/></span><span id='user" . $row['id'] . "'>" . $tpl->__('dropdown.not_assigned') . '</span>';
                            }
                            
                            $dropdownId = 'userDropdownMenuLink' . $row['id'];
                            ?>

                            <td
                                data-order="{{ $row['editorFirstname'] != '' ? $tpl->escape($row['editorFirstname']) : $tpl->__('dropdown.not_assigned') }}">
                                <x-global::forms._archive.dropdownPill class="ticketDropdown userDropdown noBg show f-left"
                                    id="{{ $dropdownId }}" :labelText="html_entity_decode($userText)" type="user" :parentId="$row['id']">
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
                            </td>

                            <?php
                            // Determine the sprint headline to display
                            $sprintHeadline = $row['sprint'] != '' && $row['sprint'] != 0 && $row['sprint'] != -1 ? $tpl->escape($row['sprintName']) : $tpl->__('links.no_list');
                            
                            $dropdownId = 'sprintDropdownMenuLink' . $row['id'];
                            ?>

                            <td data-order="{{ $sprintHeadline }}">
                                <x-global::forms._archive.dropdownPill
                                    class="ticketDropdown sprintDropdown show label-default sprint f-left"
                                    id="{{ $dropdownId }}" :labelText="$sprintHeadline" type="sprint" :parentId="$row['id']">
                                    <x-slot name="buttonText">
                                        {{ $sprintHeadline }} <i class="fa fa-caret-down" aria-hidden="true"></i>
                                    </x-slot>

                                    <x-global::actions.dropdown.item class="nav-header border">
                                        {{ __('dropdown.choose_list') }}
                                    </x-global::actions.dropdown.item>

                                    <x-global::actions.dropdown.item href="javascript:void(0);" :data-label="$tpl->__('label.not_assigned_to_list')"
                                        :data-value="$row['id'] . '_0'">
                                        {{ __('label.not_assigned_to_list') }}
                                    </x-global::actions.dropdown.item>

                                    @foreach ($tpl->get('sprints') as $sprint)
                                        <x-global::actions.dropdown.item href="javascript:void(0);" :data-label="$tpl->escape($sprint->name)"
                                            :data-value="$row['id'] . '_' . $sprint->id" :id="'ticketSprintChange' . $row['id'] . $sprint->id">
                                            {{ $tpl->escape($sprint->name) }}
                                        </x-global::actions.dropdown.item>
                                    @endforeach
                                </x-global::forms._archive.dropdownPill>
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
                            <td data-order="{{ $tpl->escape($row['planHours']) }}">
                                <x-global::forms.text-input type="text" name="planHours"
                                    value="{{ $tpl->escape($row['planHours']) }}" class="small-input secretInput"
                                    onchange="leantime.ticketsController.updatePlannedHours(this, '{{ $row['id'] }}'); jQuery(this).parent().attr('data-order', jQuery(this).val());" />
                            </td>

                            <td data-order="{{ $tpl->escape($row['hourRemaining']) }}">
                                <x-global::forms.text-input type="text" name="remainingHours"
                                    value="{{ $tpl->escape($row['hourRemaining']) }}" class="small-input secretInput"
                                    onchange="leantime.ticketsController.updateRemainingHours(this, '{{ $row['id'] }}');" />
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
                                @include('tickets::includes.ticketsubmenu', [
                                    'ticket' => $row,
                                    'onTheClock' => $onTheClock,
                                ])
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
