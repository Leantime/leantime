@extends($layout)

@section('content')
<x-globals::layout.page-header :icon="'speed'">
    @if (count($allUsers) == 1)
        <a href="#/users/newUser" class="headerCTA">
            <x-globals::elements.icon name="group" />
            <span class="tw:text-[14px] tw:leading-[25px]">
                {{ __('links.dont_do_it_alone') }}
            </span>
        </a>
    @endif

    <h5>{{ session("currentProjectClient") }}</h5>
    <h1>{!! __('headlines.project_dashboard') !!}</h1>
</x-globals::layout.page-header>

<div class="maincontent">
    {!! $tpl->displayNotification() !!}

    <div class="row">

        <div class="col-md-8">

            <div class="maincontentinner tw:z-20">

                @if ($login::userIsAtLeast($roles::$admin))
                    <x-globals::actions.dropdown-menu container-class="pull-right" data-tippy-content="{{ __('label.edit_project') }}">
                        <li>
                            <a href="{{ BASE_URL }}/projects/showProject/{{ $project['id'] }}"><x-globals::elements.icon name="edit" /> Edit Project</a>
                        </li>
                        <li>
                            <a href="{{ BASE_URL }}/projects/delProject/{{ $project['id'] }}" class="delete"><x-globals::elements.icon name="delete" /> Delete Project</a>
                        </li>
                    </x-globals::actions.dropdown-menu>
                @endif

                <x-globals::actions.dropdown-menu leading-visual="link" container-class="pull-right tw:mr-[5px]" data-tippy-content="{{ __('label.copy_url_tooltip') }}">
                    <li class="tw:p-2">
                        <x-globals::forms.text-input name="projectUrl" id="projectUrl" value="{{ BASE_URL }}/projects/changeCurrentProject/{{ $project['id'] }}" />
                        <x-globals::forms.button tag="button" type="primary" onclick="leantime.snippets.copyUrl('projectUrl')"><x-globals::elements.icon name="link" /> {{ __('label.copy_url') }}</x-globals::forms.button>
                    </li>
                </x-globals::actions.dropdown-menu>

                <a
                    href="javascript:void(0);"
                    id="favoriteProject"
                    class="btn pull-right margin-right {{ $isFavorite ? 'isFavorite' : ''}} tw:mr-[5px] round-button"
                    data-tippy-content="{{ __('label.favorite_tooltip') }}"
                ><x-globals::elements.icon name="{{ $isFavorite ? 'star' : 'star_border' }}" /></a>



                <h3>{{ session("currentProjectClient") }}</h3>

                <h1 class="articleHeadline">{{ $currentProjectName }}</h1>

                <br/>

                <x-globals::projects.checklist :progress-steps="$progressSteps" :percent-done="$percentDone" />

                <br/><br/>

                    <strong>{{ __('label.background') }}</strong><br/>
                <div class="readMoreBox">
                    <div class="tiptap-content kanbanContent closed tw:max-h-[200px] readMoreContent tw:pb-[30px]" id="projectDescription">
                        {!! $tpl->escapeMinimal($project['details']) !!}
                    </div>

                    <div class="center readMoreToggle" style="display:none;">
                        <a href="javascript:void(0)" id="descriptionReadMoreToggle">{{ __('label.read_more') }}</a>
                    </div>
                </div>


                <br/>

            </div>

            <div class="maincontentinner tw:z-10 latest-todos">
                <x-globals::forms.button link="#/tickets/newTicket" type="link" icon="add" class="action-link pull-right tw:mt-[-7px]">Create To-Do</x-globals::forms.button>
                <h5 class="subtitle">{{ __('headlines.latest_todos') }}</h5>
                <br/>
                <ul class="sortableTicketList">
                    @if (count($tickets) == 0)
                        <em>Nothing to see here. Move on.</em><br/><br/>
                    @endif

                    @foreach($tickets as $row)
                        <li class="ui-state-default" id="ticket_{!! $row['id'] !!}">
                            <div class="ticketBox fixed priority-border-{!! $row['priority'] !!}" data-val="{!! $row['id'] !!}">
                                                    <div class="timerContainer tw:py-[5px] tw:px-[15px]" id="timerContainer-{!! $row['id'] !!}">
                                        @if($row['dependingTicketId'] > 0)
                                        <a href="#/tickets/showTicket/{{  $row['dependingTicketId'] }}">
                                            {{ $row['parentHeadline'] }}
                                        </a>
                                            //
                                        @endif

                                        <a href="#/tickets/showTicket/{{ $row['id'] }}">
                                            <strong>{{ $row['headline'] }}</strong>
                                        </a>

                                        <x-globals::tickets.ticket-submenu :ticket="$row" :on-the-clock="$tpl->get('onTheClock')" />
                                    </div>

                                <div class="row">
                                    <div class="col-md-4 tw:px-[15px] tw:py-0">

                                        <x-globals::elements.icon name="business_center" class="infoIcon" data-tippy-content=" {{ __("label.due") }}" />

                                             <input
                                            type="text"
                                            title="{{ __('label.due') }}"
                                            value="{{ format($row['dateToFinish'])->date(__('text.anytime')) }}"
                                            class="duedates secretInput"
                                            data-id="{{ $row['id'] }}"
                                            name="date"
                                        />
                                    </div>
                                    <div class="col-md-8 tw:mt-[3px]">
                                        <div class="right">
                                            @php
                                                $ticketPatchUrl  = BASE_URL . '/hx/tickets/ticket/patch/' . $row['id'];
                                                $ticketHxVals    = json_encode(['id' => (string) $row['id']]);
                                                $effortSelected  = ($row['storypoints'] != '' && $row['storypoints'] > 0) ? (string) $row['storypoints'] : '';
                                                $milestoneOptions = ['' => ['name' => __('label.no_milestone'), 'class' => '#b0b0b0']];
                                                foreach ($milestones as $ms) {
                                                    $milestoneOptions[$ms->id] = ['name' => $ms->headline, 'class' => $ms->tags];
                                                }
                                            @endphp

                                            {{-- Effort chip --}}
                                            <x-globals::forms.select
                                                variant="chip"
                                                name="storypoints"
                                                :id="'effort-chip-' . $row['id']"
                                                hx-post="{{ $ticketPatchUrl }}"
                                                hx-trigger="change"
                                                hx-swap="none"
                                                hx-vals="{{ $ticketHxVals }}"
                                            >
                                                @php
                                                    $emptyLabel = __('label.effort_not_defined');
                                                    $emptyHtml  = '<span class="chip-badge state-default">' . e($emptyLabel) . '</span>';
                                                @endphp
                                                <option value="" {{ $effortSelected === '' ? 'selected' : '' }} data-chip-html="{{ $emptyHtml }}">{{ $emptyLabel }}</option>
                                                @foreach($efforts as $effortKey => $effortLabel)
                                                    @php
                                                        $effortChipHtml = '<span class="chip-badge state-default">' . e($effortLabel) . '</span>';
                                                    @endphp
                                                    <option value="{{ $effortKey }}" {{ $effortSelected === (string)$effortKey ? 'selected' : '' }} data-chip-html="{{ $effortChipHtml }}">{{ $effortLabel }}</option>
                                                @endforeach
                                            </x-globals::forms.select>

                                            {{-- Milestone chip --}}
                                            <x-globals::forms.select
                                                variant="chip"
                                                name="milestoneid"
                                                :id="'milestone-chip-' . $row['id']"
                                                hx-post="{{ $ticketPatchUrl }}"
                                                hx-trigger="change"
                                                hx-swap="none"
                                                hx-vals="{{ $ticketHxVals }}"
                                            >
                                                @foreach($milestoneOptions as $msKey => $msValue)
                                                    @php
                                                        $msName  = is_array($msValue) ? ($msValue['name'] ?? $msKey) : $msValue;
                                                        $msClass = is_array($msValue) ? ($msValue['class'] ?? '#b0b0b0') : '#b0b0b0';
                                                        $isHex   = str_starts_with((string) $msClass, '#');
                                                        $msStyle = $isHex ? 'background:' . $msClass . ';' : '';
                                                        $msChipHtml = '<span class="chip-badge state-default" style="' . $msStyle . '">' . e($msName) . '</span>';
                                                        $msSelected = (string)($row['milestoneid'] ?: '') === (string)$msKey;
                                                    @endphp
                                                    <option value="{{ $msKey }}" {{ $msSelected ? 'selected' : '' }} data-chip-html="{{ $msChipHtml }}">{{ $msName }}</option>
                                                @endforeach
                                            </x-globals::forms.select>

                                            {{-- Status chip --}}
                                            <x-globals::forms.select
                                                variant="chip"
                                                name="status"
                                                :id="'status-chip-' . $row['id']"
                                                hx-post="{{ $ticketPatchUrl }}"
                                                hx-trigger="change"
                                                hx-swap="none"
                                                hx-vals="{{ $ticketHxVals }}"
                                            >
                                                @foreach($statusLabels as $statusKey => $statusValue)
                                                    @php
                                                        $statusName  = is_array($statusValue) ? ($statusValue['name'] ?? $statusKey) : $statusValue;
                                                        $statusClass = is_array($statusValue) ? ($statusValue['class'] ?? 'label-default') : 'label-default';
                                                        $isHex       = str_starts_with((string) $statusClass, '#');
                                                        $chipBadgeClass = $isHex ? 'state-default' : $statusClass;
                                                        $chipStyle   = $isHex ? 'background:' . $statusClass . ';' : '';
                                                        $statusChipHtml = '<span class="chip-badge ' . $chipBadgeClass . '" style="' . $chipStyle . '">' . e($statusName) . '</span>';
                                                        $statusSelected = (string) $row['status'] === (string) $statusKey;
                                                    @endphp
                                                    <option value="{{ $statusKey }}" {{ $statusSelected ? 'selected' : '' }} data-chip-html="{{ $statusChipHtml }}">{{ $statusName }}</option>
                                                @endforeach
                                            </x-globals::forms.select>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="maincontentinner team-container">
                @dispatchEvent('teamBoxBeginning', ['project' => $project])

                <h5 class="subtitle">{{ __('tabs.team') }}</h5>

                <div class="row teamBox">
                    @foreach ($project['assignedUsers'] as $userId => $assignedUser)
                        <div class="col-md-3">
                            <x-users::profile-box :user="$assignedUser">
                                @spaceless
                                    @php $hasName = $assignedUser['firstname'] != '' || $assignedUser['lastname'] != ''; @endphp

                                    @if ($hasName)
                                        {{ sprintf(
                                            __('text.full_name'),
                                            $assignedUser['firstname'],
                                            $assignedUser['lastname'],
                                        ) }}
                                    @else
                                        {{ $assignedUser['username'] }}
                                    @endif

                                    <br />
                                    <small>{{ $hasName ? $assignedUser['jobTitle'] : __('label.invited') }}</small>

                                    @if ($hasName)
                                        @dispatchEvent('usercardBottom', ['user' => $assignedUser, 'project' => $project])
                                    @endif
                                @endspaceless
                            </x-users::profile-box>
                        </div>
                    @endforeach

                    @if ($login::userIsAtLeast($roles::$manager))
                        <div class="col-md-3">
                            <x-users::profile-box>
                                <a href="#/users/newUser?preSelectProjectId={{ $project['id'] }}">
                                    {{ __('links.invite_user') }}
                                </a><br/>&nbsp;
                            </x-users::profile-box>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        <div class="col-md-4">

            <div class="maincontentinner project-updates">
                <div class="pull-right">
                    @if ($login::userIsAtLeast($roles::$editor))
                        <x-globals::forms.button
                            link="javascript:void(0);"
                            type="link"
                            icon="add"
                            onclick="leantime.commentsController.toggleCommentBoxes(0);jQuery('.noCommentsMessage').toggle();"
                            id="mainToggler"
                            class="action-link tw:mt-[-7px]"
                        >{{ __('links.add_new_report') }}</x-globals::forms.button>
                    @endif
                </div>

                <h5 class="subtitle">{{ __('subtitles.project_updates') }}</h5>

                <form method="post" action="{{ BASE_URL }}/dashboard/show">
                    <input type="hidden" name="comment" value="1" />
                        @if ($login::userIsAtLeast($roles::$editor))
                            <div id="comment0" class="commentBox tw:hidden">
                                <label for="projectStatus tw:inline">{{ __('label.project_status_is') }}</label>

                                <x-globals::forms.select name="status" id="projectStatus" class="tw:ml-0 tw:mb-[10px]">
                                    <option value="green">{{ __('label.project_status_green') }}</option>
                                    <option value="yellow">{{ __('label.project_status_yellow') }}</option>
                                    <option value="red">{{ __('label.project_status_red') }}</option>
                                </x-globals::forms.select>

                                <div class="commentReply">
                                    <label for="dashboard-note-editor" class="sr-only">{{ __('label.add_note') }}</label>
                                    <textarea rows="5" cols="50" id="dashboard-note-editor" class="tiptapSimple tw:w-full" name="text" aria-label="{{ __('label.add_note') }}"></textarea>
                                    <x-globals::forms.button submit type="success" tag="button" class="tw:ml-0" name="comment">{{ __('buttons.save') }}</x-globals::forms.button>
                                    <a
                                        href="javascript:void(0);"
                                        onclick="leantime.commentsController.toggleCommentBoxes(-1);jQuery('.noCommentsMessage').toggle();"
                                        class="tw:leading-[50px]"
                                    >{{ __('links.cancel') }}</a>
                                    <input type="hidden" name="comment" value="1"/>
                                    <input type="hidden" name="father" id="father" value="0"/>
                                </div>
                            </div>
                        @endif

                        <div id="comments">
                            @foreach ($comments as $row)
                                @if ($loop->iteration == 3)
                                    <a href="javascript:void(0);" onclick="jQuery('.readMore').toggle('fast')">
                                        {{ __('links.read_more') }}
                                    </a>
                                    <div class="readMore tw:hidden tw:mt-[20px]">
                                @endif
                                <div class="clearall">
                                    <div>
                                        <div class="commentContent statusUpdate commentStatus-{{ $row['status'] }}">
                                            <strong class="fancyLink">
                                                {{ sprintf(
                                                    __('text.report_written_on'),
                                                    format($row['date'])->date(),
                                                    format($row['date'])->time()
                                                ) }}
                                            </strong>
                                                @if ($login::userIsAtLeast($roles::$editor))
                                                    <x-globals::actions.dropdown-menu container-class="tw:float-right tw:ml-[10px]">
                                                        @if ($row['userId'] == session("userdata.id"))
                                                            <li>
                                                                <a href="{!! $delUrlBase . $row['id'] !!}" class="deleteComment">
                                                                    <x-globals::elements.icon name="delete" /> {{ __('links.delete') }}
                                                                </a>
                                                            </li>
                                                        @endif

                                                        @isset($ticket->id)
                                                            <li>
                                                                <a
                                                                    href="javascript:void(0);"
                                                                    onclick="leantime.ticketsController.addCommentTimesheetContent({!! $row['id'] !!}, {!! $ticket->id !!})"
                                                                ><x-globals::elements.icon name="schedule" /> {{ __('label.add_to_timesheet') }}</a>
                                                            </li>
                                                        @endif
                                                    </x-globals::actions.dropdown-menu>
                                                @endif

                                            <div class="text" id="commentText-{{ $row['id'] }}">{!! $tpl->escapeMinimal($row['text']) !!}</div>
                                        </div>

                                        <div class="commentLinks">
                                            <small class="right">
                                                {!! sprintf(
                                                    __('text.written_on_by'),
                                                    format($row['date'])->date(),
                                                    format($row['date'])->time(),
                                                    $tpl->escape($row['firstname']),
                                                    $tpl->escape($row['lastname'])
                                                ) !!}
                                            </small>

                                            @if ($login::userIsAtLeast($roles::$commenter))
                                                <a
                                                    href="javascript:void(0);"
                                                    onclick="leantime.commentsController.toggleCommentBoxes({!! $row['id'] !!});"
                                                ><x-globals::elements.icon name="reply" /> {{ __('links.reply') }}
                                                </a>
                                            @endif
                                        </div>

                                        <div class="replies">
                                            @if ($row['replies'])
                                                @foreach ($row['replies'] as $comment)
                                                    <x-comments::reply :comment="$comment" :iteration="$loop->iteration" />
                                                @endforeach
                                            @endif
                                            <x-comments::input :commentId="$row['id']" :user="session('userdata')" />
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            @if (count($comments) >= 3)
                                </div>
                            @endif
                        </div>

                    @if (count($comments) == 0)
                        <div class="noCommentsMessage tw:pl-0 tw:clear-both">
                                {{ __('text.no_updates') }}
                        </div>
                    @endif
                    <div class="clearall"></div>
                </form>
                <div class="clearall"></div>
            </div>

            <div class="maincontentinner project-progress">
                <div id="projectProgressContainer">
                        <h5 class="subtitle">{{ __('subtitles.project_progress') }}</h5>

                        <div id="canvas-holder" class="tw:w-full tw:h-[250px]">
                            <canvas id="chart-area"></canvas>
                        </div>

                        <br/><br/>
                </div>

                <div id="milestoneProgressContainer">
                        <h5 class="subtitle">{{ __('headline.milestones') }}</h5>
                        <ul class="sortableTicketList">
                            @if (count($milestones) == 0)
                                <div class="center">
                                    <br/>
                                    <h4>{{ __('headlines.no_milestones') }}</h4>
                                    {{ __('text.milestones_help_organize_projects') }}
                                    <br/><br/>
                                    <a href="{{ BASE_URL }}/tickets/roadmap">{!! __('links.goto_milestones') !!}</a>
                                </div>
                            @endif

                            @foreach($milestones as $row)
                                @if ($row->percentDone >= 100 && (new \DateTime($row->editTo) < new \DateTime()))
                                    @break
                                @endif

                                <li class="ui-state-default" id="milestone_{!! $row->id !!}">

                                    <div hx-trigger="load"
                                         hx-indicator=".htmx-indicator"
                                         hx-target="this"
                                         hx-swap="innerHTML"
                                         hx-get="<?= BASE_URL ?>/hx/tickets/milestones/showCard?milestoneId=<?= $row->id ?>"
                                         aria-live="polite">
                                        <div class="htmx-indicator" role="status">
                                                <?= $tpl->__('label.loading_milestone') ?>
                                        </div>
                                    </div>

                                </li>
                            @endforeach
                        </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@once @push('scripts')
<script type='text/javascript'>
jQuery(document).ready(function(){
    if (window.leantime && window.leantime.tiptapController) {
        leantime.tiptapController.initSimpleEditor();
    }
});
</script>
@endpush @endonce

@once @push('scripts')
<script>
    @dispatchEvent('scripts.afterOpen')

    jQuery(document).ready(function () {

        jQuery('#descriptionReadMoreToggle').click(function() {

            if (jQuery("#projectDescription").hasClass("closed")) {
                jQuery("#projectDescription").css("max-height", "100%");
                jQuery("#projectDescription").removeClass("closed");
                jQuery("#projectDescription").removeClass("kanbanContent");
                jQuery('#descriptionReadMoreToggle').text("{{ __('label.read_less') }}");
            } else {
                jQuery("#projectDescription").css("max-height", "200px");
                jQuery("#projectDescription").addClass("closed");
                jQuery("#projectDescription").addClass("kanbanContent");
                jQuery('#descriptionReadMoreToggle').text("{{ __('label.read_more') }}");
            }
        });

        jQuery(".readMoreBox").each(function() {
            if (jQuery(this).find(".readMoreContent").height() >= 169) {

                jQuery(this).find(".readMoreToggle").show();
            }
        });

        jQuery(document).on('click', '.progressWrapper .dropdown-menu', function (e) {
            e.stopPropagation();
        });

        @if ($login::userIsAtLeast($roles::$editor))
            leantime.dashboardController.prepareHiddenDueDate();
            leantime.ticketsController.initEffortDropdown();
            leantime.ticketsController.initMilestoneDropdown();
            leantime.ticketsController.initStatusDropdown();
            leantime.usersController.initUserEditModal();
        @else
            leantime.authController.makeInputReadonly(".maincontent");
        @endif

        leantime.dashboardController.initProgressChart(
            "chart-area",
            {!! round($projectProgress['percent']) !!},
            {!! round(100 - $projectProgress['percent']) !!}
        );

        jQuery("#favoriteProject").click(function() {
            if (jQuery("#favoriteProject").hasClass("isFavorite")) {
                leantime.reactionsController.removeReaction(
                    'project',
                    {!! $project['id'] !!},
                    'favorite',
                    function() {
                        jQuery("#favoriteProject").find(".material-symbols-outlined").text("star_border");
                        jQuery("#favoriteProject").removeClass("isFavorite");
                    }
                );
            } else {
                leantime.reactionsController.addReactions(
                    'project',
                    {!! $project['id'] !!},
                    'favorite',
                    function() {
                        jQuery("#favoriteProject").find(".material-symbols-outlined").text("star");
                        jQuery("#favoriteProject").addClass("isFavorite");
                    }
                );
            }
        });

        leantime.ticketsController.initDueDateTimePickers();
        leantime.ticketsController.initDueDateTimePickers();


        @php(session(["usersettings.modals.projectDashboardTour" => 1]));
    });

    @dispatchEvent('scripts.beforeClose')
</script>
@endpush @endonce

@endsection
