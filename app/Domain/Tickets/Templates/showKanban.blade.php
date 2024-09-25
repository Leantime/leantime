@extends($layout)

@section('content')

    <?php
    $tickets = $tpl->get('tickets');
    $sprints = $tpl->get('sprints');
    $searchCriteria = $tpl->get('searchCriteria');
    $currentSprint = $tpl->get('currentSprint');
    
    $todoTypeIcons = $tpl->get('ticketTypeIcons');
    
    $efforts = $tpl->get('efforts');
    $priorities = $tpl->get('priorities');
    
    $allTicketGroups = $tpl->get('allTickets');
    
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

                </div>
            </div>

            <div class="clearfix"></div>


            <?php if (isset($allTicketGroups['all'])) {
                $allTickets = $allTicketGroups['all']['items'];
            }
            ?>
            <div class=""
                style="
            display: flex;
            position: sticky;
            top: 110px;
            justify-content: flex-start;
            z-index: 9;
            ">
                <?php foreach ($tpl->get('allKanbanColumns') as $key => $statusRow) { ?>
                <div class="column">

                    <h4 class="widgettitle title-primary title-border-<?php echo $statusRow['class']; ?>">

                        <?php if ($login::userIsAtLeast($roles::$manager)) { ?>
                        <!-- Determine Label Text for the Dropdown -->
                        @php
                            $labelText = '<i class="fa fa-ellipsis-v" aria-hidden="true"></i>';
                        @endphp

                        <!-- Context Menu Component -->
                        <x-global::content.context-menu :label-text="$labelText" contentRole="link" position="bottom"
                            align="start">
                            <!-- Dropdown Items -->
                            <x-global::actions.dropdown.item
                                href="#/setting/editBoxLabel?module=ticketlabels&label={{ $key }}"
                                class="editLabelModal">
                                {!! __('headlines.edit_label') !!}
                            </x-global::actions.dropdown.item>
                            <x-global::actions.dropdown.item
                                href="{{ BASE_URL }}/projects/showProject/{{ session('currentProject') }}#todosettings">
                                {!! __('links.add_remove_col') !!}
                            </x-global::actions.dropdown.item>
                        </x-global::content.context-menu>

                        <?php } ?>

                        <strong class="count">0</strong>
                        <?php $tpl->e($statusRow['name']); ?>

                    </h4>

                    <div class="" style="margin-top: 1rem;">
                        <a href="javascript:void(0);" style="padding:10px; display:block; width:100%;"
                            id="ticket_new_link_<?= $key ?>"
                            onclick="jQuery('#ticket_new_link_<?= $key ?>').toggle('fast'); jQuery('#ticket_new_<?= $key ?>').toggle('fast', function() { jQuery(this).find('input[name=headline]').focus(); });">
                            <i class="fas fa-plus-circle"></i> Add To-Do</a>

                        <div class="hideOnLoad " id="ticket_new_<?= $key ?>" style="padding-top:5px; padding-bottom:5px;">

                            <form method="post">
                                <x-global::forms.text-input 
                                    type="text" 
                                    name="headline" 
                                    placeholder="Enter To-Do Title" 
                                    title="{!! $tpl->__('label.headline') !!}" 
                                    variant=""
                                    class="rounded-full"
                                 />
                                
                                <input type="hidden" name="milestone" value="{!! $searchCriteria['milestone'] !!}" />
                                <input type="hidden" name="status" value="{!! $key !!}" />
                                <input type="hidden" name="sprint" value="{!! session('currentSprint') !!}" />
                                
                                <x-global::forms.button 
                                    type="submit" 
                                    name="quickadd"
                                >
                                    Save
                                </x-global::forms.button>
                                
                                <x-global::forms.button 
                                    tag="a"
                                    class="btn btn-default"
                                    content-role="secondary"
                                    href="javascript:void(0);" 
                                    onclick="jQuery('#ticket_new_{!! $key !!}, #ticket_new_link_{!! $key !!}').toggle('fast');"
                                >
                                    {!! $tpl->__('links.cancel') !!}
                                </x-global::forms.button>
                                
                            </form>

                            <div class="clearfix"></div>
                        </div>

                    </div>
                </div>
                <?php } ?>
            </div>

            <?php foreach ($allTicketGroups as $group) {?>
            <?php
            $allTickets = $group['items'];
            ?>

            <?php if ($group['label'] != 'all') { ?>
            <h5 class="accordionTitle kanbanLane <?= $group['class'] ?>" id="accordion_link_<?= $group['id'] ?>">
                <a href="javascript:void(0)" class="accordion-toggle" id="accordion_toggle_<?= $group['id'] ?>"
                    onclick="leantime.snippets.accordionToggle('<?= $group['id'] ?>');">
                    <i class="fa fa-angle-down"></i><?= $group['label'] ?> (<?= count($group['items']) ?>)
                </a>
            </h5>
            <div class="simpleAccordionContainer kanban" id="accordion_content-<?= $group['id'] ?>">
                <?php } ?>

                <div class="sortableTicketList kanbanBoard" id="kanboard-<?= $group['id'] ?>" style="margin-top:-5px;">

                    <div class="row-fluid">

                        <?php foreach ($tpl->get('allKanbanColumns') as $key => $statusRow) { ?>
                        <div class="column">
                            <div class="contentInner <?php echo 'status_' . $key; ?>">
                                <?php foreach ($allTickets as $row) { ?>
                                <?php if ($row["status"] == $key) {?>
                                <div class="ticketBox moveable container priority-border-<?= $row['priority'] ?>"
                                    id="ticket_<?php echo $row['id']; ?>">

                                    <div class="row">

                                        <div class="col-md-12">


                                            @include('tickets::includes.ticketsubmenu', [
                                                'ticket' => $row,
                                                'onTheClock' => $onTheClock,
                                            ])



                                            <?php if ($row['dependingTicketId'] > 0) { ?>
                                            <small><a href="#/tickets/showTicket/<?= $row['dependingTicketId'] ?>"
                                                    class="form-modal"><?= $tpl->escape($row['parentHeadline']) ?></a></small>
                                            //
                                            <?php } ?>
                                            <small><i class="fa <?php echo $todoTypeIcons[strtolower($row['type'])]; ?>"></i> <?php echo $tpl->__('label.' . strtolower($row['type'])); ?></small>
                                            <small>#<?php echo $row['id']; ?></small>
                                            <div class="kanbanCardContent">
                                                <h4><a
                                                        href="#/tickets/showTicket/<?php echo $row['id']; ?>"><?php $tpl->e($row['headline']); ?></a>
                                                </h4>

                                                <div class="kanbanContent" style="margin-bottom: 20px">
                                                    <?php echo $tpl->escapeMinimal($row['description']); ?>
                                                </div>

                                            </div>
                                            @if ($row['dateToFinish'] != "0000-00-00 00:00:00" && $row['dateToFinish'] != "1969-12-31 00:00:00")

                                            <x-global::forms.text-input 
                                                type="text" 
                                                name="date" 
                                                value="{!! format($row['dateToFinish'])->date() !!}" 
                                                title="{{ __('label.due') }}" 
                                                class="duedates secretInput" 
                                                style="margin-left: 0px;" 
                                                data-id="{!! $row['id'] !!}" 
                                                leadingVisual="{!! $tpl->__('label.due_icon') !!}" 
                                            />
                                        

                                            @endif
                                        </div>
                                    </div>

                                    <div class="clearfix" style="padding-bottom: 8px;"></div>

                                    <div class="timerContainer " id="timerContainer-<?php echo $row['id']; ?>">

                                        <div class="dropdown ticketDropdown milestoneDropdown colorized show firstDropdown">
                                            <!-- Determine Label Text for the Dropdown -->
                                            @php
                                                $milestoneLabelText = '<span class="text">';
                                                if ($row['milestoneid'] != '' && $row['milestoneid'] != 0) {
                                                    $milestoneLabelText .= $tpl->escape($row['milestoneHeadline']);
                                                } else {
                                                    $milestoneLabelText .= $tpl->__('label.no_milestone');
                                                }
                                                $milestoneLabelText .=
                                                    '</span>&nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>';
                                            @endphp

                                            <!-- Dropdown Component -->
                                            <x-global::actions.dropdown :label-text="$milestoneLabelText" contentRole="link"
                                                position="bottom" align="start">
                                                <!-- Dropdown Items -->
                                                <x-slot:menu>
                                                    <li class="nav-header border">
                                                        {{ $tpl->__('dropdown.choose_milestone') }}</li>
                                                    <x-global::actions.dropdown.item style="background-color: #b0b0b0"
                                                        href="javascript:void(0);"
                                                        data-label="{{ $tpl->__('label.no_milestone') }}"
                                                        data-value="{{ $row['id'] . '_0_#b0b0b0' }}">
                                                        {{ $tpl->__('label.no_milestone') }}
                                                    </x-global::actions.dropdown.item>
                                                    @foreach ($tpl->get('milestones') as $milestone)
                                                        <x-global::actions.dropdown.item href="javascript:void(0);"
                                                            data-label="{{ $tpl->escape($milestone->headline) }}"
                                                            data-value="{{ $row['id'] . '_' . $milestone->id . '_' . $tpl->escape($milestone->tags) }}"
                                                            id="ticketMilestoneChange{{ $row['id'] . $milestone->id }}"
                                                            style="background-color: {{ $tpl->escape($milestone->tags) }}">
                                                            {{ $tpl->escape($milestone->headline) }}
                                                        </x-global::actions.dropdown.item>
                                                    @endforeach
                                                </x-slot:menu>
                                            </x-global::actions.dropdown>

                                        </div>


                                        <?php if ($row['storypoints'] != '' && $row['storypoints'] > 0) { ?>
                                        <!-- Determine Label Text for the Dropdown -->
                                        @php
                                            $effortLabelText = '<span class="text">';
                                            if ($row['storypoints'] != '' && $row['storypoints'] > 0) {
                                                $effortLabelText .=
                                                    $efforts['' . $row['storypoints']] ?? $row['storypoints'];
                                            } else {
                                                $effortLabelText .= $tpl->__('label.story_points_unkown');
                                            }
                                            $effortLabelText .=
                                                '</span>&nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>';
                                        @endphp

                                        <!-- Dropdown Component -->
                                        <x-global::actions.dropdown :label-text="$effortLabelText" contentRole="link" position="bottom"
                                            align="start">
                                            <!-- Dropdown Items -->
                                            <x-slot:menu>
                                                <li class="nav-header border">{{ $tpl->__('dropdown.how_big_todo') }}</li>
                                                @foreach ($efforts as $effortKey => $effortValue)
                                                    <x-global::actions.dropdown.item href="javascript:void(0);"
                                                        data-value="{{ $row['id'] . '_' . $effortKey }}"
                                                        id="ticketEffortChange{{ $row['id'] . $effortKey }}">
                                                        {{ $effortValue }}
                                                    </x-global::actions.dropdown.item>
                                                @endforeach
                                            </x-slot:menu>
                                        </x-global::actions.dropdown>

                                        <?php } ?>


                                        <div class="dropdown ticketDropdown priorityDropdown show">
                                            <!-- Determine Label Text for the Dropdown -->
                                            @php
                                                $priorityLabelText = '<span class="text">';
                                                if ($row['priority'] != '' && $row['priority'] > 0) {
                                                    $priorityLabelText .= $priorities[$row['priority']];
                                                } else {
                                                    $priorityLabelText .= $tpl->__('label.priority_unkown');
                                                }
                                                $priorityLabelText .=
                                                    '</span>&nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>';
                                            @endphp

                                            <!-- Dropdown Component -->
                                            <x-global::actions.dropdown :label-text="$priorityLabelText" contentRole="link"
                                                position="bottom" align="start">
                                                <!-- Dropdown Items -->
                                                <x-slot:menu>
                                                    <li class="nav-header border">
                                                        {{ $tpl->__('dropdown.select_priority') }}</li>
                                                    @foreach ($priorities as $priorityKey => $priorityValue)
                                                        <x-global::actions.dropdown.item href="javascript:void(0);"
                                                            class="priority-bg-{{ $priorityKey }}"
                                                            data-value="{{ $row['id'] . '_' . $priorityKey }}"
                                                            id="ticketPriorityChange{{ $row['id'] . $priorityKey }}">
                                                            {{ $priorityValue }}
                                                        </x-global::actions.dropdown.item>
                                                    @endforeach
                                                </x-slot:menu>
                                            </x-global::actions.dropdown>

                                        </div>


                                        <div
                                            class="dropdown ticketDropdown userDropdown noBg show right lastDropdown dropRight">
                                            @php
                                                // Determine the label text dynamically
                                                $userLabelText = '<span class="text">';
                                                if ($row['editorFirstname'] != '') {
                                                    $userLabelText .=
                                                        "<span id='userImage" .
                                                        $row['id'] .
                                                        "'><img src='" .
                                                        BASE_URL .
                                                        '/api/users?profileImage=' .
                                                        $row['editorId'] .
                                                        "' width='25' style='vertical-align: middle;'/></span>";
                                                } else {
                                                    $userLabelText .=
                                                        "<span id='userImage" .
                                                        $row['id'] .
                                                        "'><img src='" .
                                                        BASE_URL .
                                                        "/api/users?profileImage=false' width='25' style='vertical-align: middle;'/></span>";
                                                }
                                                $userLabelText .= '</span>';
                                            @endphp

                                            <!-- Dropdown Component -->
                                            <x-global::actions.dropdown :label-text="$userLabelText" contentRole="link"
                                                position="bottom" align="start">
                                                <!-- Dropdown Items -->
                                                <x-slot:menu>
                                                    <li class="nav-header border">{{ $tpl->__('dropdown.choose_user') }}
                                                    </li>
                                                    @if (is_array($tpl->get('users')))
                                                        @foreach ($tpl->get('users') as $user)
                                                            <x-global::actions.dropdown.item href="javascript:void(0);"
                                                                data-label="{{ sprintf($tpl->__('text.full_name'), $tpl->escape($user['firstname']), $tpl->escape($user['lastname'])) }}"
                                                                data-value="{{ $row['id'] . '_' . $user['id'] . '_' . $user['profileId'] }}"
                                                                id="userStatusChange{{ $row['id'] . $user['id'] }}">
                                                                <img src="{{ BASE_URL }}/api/users?profileImage={{ $user['id'] }}"
                                                                    width="25"
                                                                    style="vertical-align: middle; margin-right:5px;" />
                                                                {{ sprintf($tpl->__('text.full_name'), $tpl->escape($user['firstname']), $tpl->escape($user['lastname'])) }}
                                                            </x-global::actions.dropdown.item>
                                                        @endforeach
                                                    @endif
                                                </x-slot:menu>
                                            </x-global::actions.dropdown>

                                        </div>

                                    </div>
                                    <div class="clearfix"></div>

                                    <?php if ($row["commentCount"] > 0 || $row["subtaskCount"] > 0 || $row['tags'] != '') {?>
                                    <div class="row">

                                        <div class="col-md-12 border-top" style="white-space: nowrap;">
                                            <?php if ($row["commentCount"] > 0) {?>
                                            <a href="#/tickets/showTicket/<?php echo $row['id']; ?>"><span
                                                    class="fa-regular fa-comments"></span> <?php echo $row['commentCount']; ?></a>&nbsp;
                                            <?php } ?>

                                            <?php if ($row["subtaskCount"] > 0) {?>
                                            <a id="subtaskLink_<?php echo $row['id']; ?>"
                                                href="#/tickets/showTicket/<?php echo $row['id']; ?>" class="subtaskLineLink">
                                                <span class="fa fa-diagram-successor"></span>
                                                <?php echo $row['subtaskCount']; ?></a>&nbsp;
                                            <?php } ?>
                                            <?php if ($row['tags'] != '') {?>
                                            <?php $tagsArray = explode(',', $row['tags']); ?>
                                            <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">
                                                <i class="fa fa-tags" aria-hidden="true"></i> <?= count($tagsArray) ?>
                                            </a>
                                            <ul class="dropdown-menu ">
                                                <li style="padding:10px">
                                                    <div class='tagsinput readonly'>
                                                        <?php
                                                        
                                                        foreach ($tagsArray as $tag) {
                                                            echo "<span class='tag'><span>" . $tpl->escape($tag) . '</span></span>';
                                                        }
                                                        
                                                        ?>
                                                    </div>
                                                </li>
                                            </ul>
                                            <?php } ?>

                                        </div>

                                    </div>
                                    <?php } ?>

                                </div>
                                <?php } ?>
                                <?php } ?>
                            </div>

                        </div>
                        <?php } ?>
                        <div class="clearfix"></div>

                    </div>
                </div>

                <?php if ($group['label'] != 'all') { ?>
            </div>
            <?php } ?>

            <?php } ?>

        </div>

    </div>

    <script type="text/javascript">
        jQuery(document).ready(function() {

            <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
            leantime.ticketsController.initUserDropdown();
            leantime.ticketsController.initMilestoneDropdown();
            leantime.ticketsController.initDueDateTimePickers();
            leantime.ticketsController.initEffortDropdown();
            leantime.ticketsController.initPriorityDropdown();


            var ticketStatusList = [<?php foreach ($tpl->get('allTicketStates') as $key => $statusRow) {
                echo "'" . $key . "',";
            } ?>];
            leantime.ticketsController.initTicketKanban(ticketStatusList);

            <?php } else { ?>
            leantime.authController.makeInputReadonly(".maincontentinner");
            <?php } ?>

            leantime.ticketsController.setUpKanbanColumns();

            <?php if (isset($_GET['showTicketModal'])) {
            if ($_GET['showTicketModal'] == "") {
                $modalUrl = "";
            } else {
                $modalUrl = "/" . (int)$_GET['showTicketModal'];
            }
            ?>

            <?php } ?>


            <?php foreach ($allTicketGroups as $group) {

            foreach ($group['items'] as $ticket) {
                if ($ticket['dependingTicketId'] > 0) {
                    ?>
            var startElement = document.getElementById('subtaskLink_<?= $ticket['dependingTicketId'] ?>');
            var endElement = document.getElementById('ticket_<?= $ticket['id'] ?>');


            if (startElement != undefined && endElement != undefined) {

                var startAnchor = LeaderLine.mouseHoverAnchor({
                    element: startElement,
                    showEffectName: 'draw',
                    style: {
                        background: 'none',
                        backgroundColor: 'none'
                    },
                    hoverStyle: {
                        background: 'none',
                        backgroundColor: 'none',
                        cursor: 'pointer'
                    }
                });

                var line<?= $ticket['id'] ?> = new LeaderLine(startAnchor, endElement, {
                    startPlugColor: 'var(--accent1)',
                    endPlugColor: 'var(--accent2)',
                    gradient: true,
                    size: 2,
                    path: "grid",
                    startSocket: 'bottom',
                    endSocket: 'auto'
                });

                jQuery("#ticket_<?= $ticket['id'] ?>").mousedown(function() {

                    })
                    .mousemove(function() {

                    })
                    .mouseup(function() {
                        line<?= $ticket['id'] ?>.position();
                    });

                jQuery("#ticket_<?= $ticket['dependingTicketId'] ?>").mousedown(function() {

                    })
                    .mousemove(function() {


                    })
                    .mouseup(function() {
                        line<?= $ticket['id'] ?>.position();

                    });

            }

            <?php }
            }
        } ?>




        });
    </script>
@endsection
