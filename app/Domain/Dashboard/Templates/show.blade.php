@extends($layout)

@section('content')
<x-global::pageheader :icon="'fa fa-gauge-high'">
    @if (count($allUsers) == 1)
        <a href="#/users/newUser" class="headerCTA">
            <i class="fa fa-users"></i>
            <span class="tw-text-[14px] tw-leading-[25px]">
                {{ __('links.dont_do_it_alone') }}
            </span>
        </a>
    @endif

    <h5>{{ session("currentProjectClient") }}</h5>
    <h1>{!! __('headlines.project_dashboard') !!}</h1>
</x-global::pageheader>

<div class="maincontent">
    {!! $tpl->displayNotification() !!}

    <div class="row">

        <div class="col-md-8">

            <div class="maincontentinner tw-z-20">

                @if ($login::userIsAtLeast($roles::$admin))
                    <div class="pull-right dropdownWrapper">
                        <a
                            class="dropdown-toggle btn round-button"
                            data-toggle="dropdown"
                            data-tippy-content="{{ __('label.edit_project') }}"
                            href="{{ BASE_URL }}/projects/showProject/{{ $project['id'] }}"
                        ><i class="fa fa-ellipsis"></i></a>
                        <ul class="dropdown-menu">
                            <li class="dropdown-item">
                                <a
                                    href="{{ BASE_URL }}/projects/showProject/{{ $project['id'] }}"

                                ><i class="fa fa-edit"></i> Edit Project</a>
                            </li>
                            <li class="dropdown-item">
                                <a
                                    href="{{ BASE_URL }}/projects/delProject/{{ $project['id'] }}"
                                    class="delete"

                                ><i class="fa fa-trash"></i> Delete Project</a>
                            </li>

                        </ul>
                    </div>
                @endif

                <div class="pull-right dropdownWrapper tw-mr-[5px]">
                    <a
                        class="dropdown-toggle btn round-button"
                        data-toggle="dropdown"
                        data-tippy-content="{{ __('label.copy_url_tooltip') }}"
                        href="{{ BASE_URL }}/project/changeCurrentProject/{{ $project['id'] }}"
                    ><i class="fa fa-link"></i></a>
                    <div class="dropdown-menu padding-md">
                        <input type="text" id="projectUrl" value="{{ BASE_URL }}/projects/changeCurrentProject/{{ $project['id'] }}" />
                        <button class="btn btn-primary" onclick="leantime.snippets.copyUrl('projectUrl')">{{ __('links.copy_url') }}</button>
                    </div>
                </div>

                <a
                    href="javascript:void(0);"
                    id="favoriteProject"
                    class="btn pull-right margin-right {{ $isFavorite ? 'isFavorite' : ''}} tw-mr-[5px] round-button"
                    data-tippy-content="{{ __('label.favorite_tooltip') }}"
                ><i class="{{ $isFavorite ? 'fa-solid' : 'fa-regular' }} fa-star"></i></a>



                <h3>{{ session("currentProjectClient") }}</h3>

                <h1 class="articleHeadline">{{ $currentProjectName }}</h1>

                <br/>

                @include('projects::partials.checklist', [
                    'progressSteps' => $progressSteps,
                    'percentDone' => $percentDone
                ])

                <br/><br/>

                    <strong>{{ __('label.background') }}</strong><br/>
                <div class="readMoreBox">
                    <div class="mce-content-body kanbanContent closed tw-max-h-[200px] readMoreContent tw-pb-[30px]" id="projectDescription">
                        {!! $tpl->escapeMinimal($project['details']) !!}
                    </div>

                    <div class="center readMoreToggle" style="display:none;">
                        <a href="javascript:void(0)" id="descriptionReadMoreToggle">{{ __('label.read_more') }}</a>
                    </div>
                </div>


                <br/>

            </div>

            <div class="maincontentinner tw-z-10">
                <a href="#/tickets/newTicket" class="btn btn-link action-link pull-right" style="margin-top:-7px;"><i class="fa fa-plus"></i> Create To-Do</a>
                <h5 class="subtitle">{{ __('headlines.latest_todos') }}</h5>
                <br/>
                <ul class="sortableTicketList">
                    @if (count($tickets) == 0)
                        <em>Nothing to see here. Move on.</em><br/><br/>
                    @endif

                    @foreach($tickets as $row)
                        <li class="ui-state-default" id="ticket_{!! $row['id'] !!}">
                            <div class="ticketBox fixed priority-border-{!! $row['priority'] !!}" data-val="{!! $row['id'] !!}">
                                <div class="row">
                                    <div class="col-md-12 timerContainer tw-py-[5px] tw-px-[15px]" id="timerContainer-{!! $row['id'] !!}">
                                        @if($row['dependingTicketId'] > 0)
                                        <a href="#/tickets/showTicket/{{  $row['dependingTicketId'] }}">
                                            {{ $row['parentHeadline'] }}
                                        </a>
                                            //
                                        @endif

                                        <a href="#/tickets/showTicket/{{ $row['id'] }}">
                                            <strong>{{ $row['headline'] }}</strong>
                                        </a>

                                        @include("tickets::partials.ticketsubmenu", ["ticket" => $row,"onTheClock" => $tpl->get("onTheClock")])
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 tw-px-[15px] tw-py-0">

                                        <i class="fa-solid fa-business-time infoIcon" data-tippy-content=" {{ __("label.due") }}"></i>

                                             <input
                                            type="text"
                                            title="{{ __('label.due') }}"
                                            value="{{ format($row['dateToFinish'])->date(__('text.anytime')) }}"
                                            class="duedates secretInput"
                                            data-id="{{ $row['id'] }}"
                                            name="date"
                                        />
                                    </div>
                                    <div class="col-md-8 tw-mt-[3px]">
                                        <div class="right">
                                            <div class="dropdown ticketDropdown effortDropdown show">
                                                <a
                                                    class="dropdown-toggle f-left label-default effort"
                                                    href="javascript:void(0);"
                                                    role="button"
                                                    id="effortDropdownMenuLink{{ $row['id'] }}"
                                                    data-toggle="dropdown"
                                                    aria-haspopup="true"
                                                    aria-expanded="false"
                                                ><span class="text">
                                                     {{ $row['storypoints'] != '' && $row['storypoints'] > 0
                                                            ? $efforts[''.$row['storypoints'].'']
                                                            : __('label.story_points_unkown')
                                                        }}
                                                </span>&nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i></a>

                                                <ul class="dropdown-menu" aria-labelledby="effortDropdownMenuLink{{ $row['id'] }}">
                                                    <li class="nav-header border">{{ __('dropdown.how_big_todo') }}</li>
                                                    @foreach ($efforts as $effortKey => $effortValue)
                                                        <li class="dropdown-item">
                                                            <a
                                                                href="javascript:void(0)"
                                                                data-value="{{ $row['id'] }}_{{ $effortKey}}"
                                                                id="ticketEffortChange_{{ $row['id'] . $effortKey }}"
                                                            >{{ $effortValue }}</a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>

                                            <div class="dropdown ticketDropdown milestoneDropdown colorized show">
                                                <a
                                                    style="background-color:{{ __($row['milestoneColor']) }}"
                                                    class="dropdown-toggle f-left label-default milestone"
                                                    href="javascript:void(0);"
                                                    role="button"
                                                    id="milestoneDropdownMenuLink{{ $row['id'] }}"
                                                    data-toggle="dropdown"
                                                    aria-haspopup="true"
                                                    aria-expanded="false"
                                                ><span class="text">
                                                    {{ $row['milestoneid'] != '' && $row['milestoneid'] != 0
                                                        ? $row['milestoneHeadline']
                                                        : __('label.no_milestone')
                                                    }}
                                                </span>&nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i></a>

                                                <ul class="dropdown-menu" aria-labeledby="milestoneDropdownMenuLink{{ $row['id'] }}">
                                                    <li class="nav-header border">{{ __('dropdown.choose_milestone') }}</li>
                                                    <li class="dropdown-item">
                                                        <a
                                                            href="javascript:void(0);"
                                                            data-label="{{ __('label.no_milestone') }}"
                                                            data-value="{{ $row['id'] }}_0_#b0b0b0"
                                                            class="tw-bg-[#b0b0b0]"
                                                        >{{ __('label.no_milestone') }}</a>
                                                    </li>
                                                    @foreach ($milestones as $milestone)
                                                        <li class="dropdown-item">
                                                            <a
                                                                href="javascript:void(0);"
                                                                data-label="{{ $milestone->headline }}"
                                                                data-value="{{ $row['id'] }}_{!! $milestone->id !!}_{{ $milestone->tags }}"
                                                                id="ticketMilestoneChange_{{ $row['id'] . $milestone->id }}"
                                                                style="background-color:{{ $milestone->tags }}"
                                                            >{{ $milestone->headline }}</a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>

                                            <div class="dropdown ticketDropdown statusDropdown colorized show">
                                                <a
                                                    class="dropdown-toggle f-left status {!! $statusLabels[$row['status']]['class'] !!}"
                                                    href="javascript:void(0);"
                                                    role="button"
                                                    id="statusDropdownMenuLink{{ $row['id'] }}"
                                                    data-toggle="dropdown"
                                                    aria-haspopup="true"
                                                    aria-expanded="false"
                                                ><span class="text">{!! $statusLabels[$row['status']]['name'] !!}</span>&nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i></a>

                                                <ul class="dropdown-menu" aria-labelledby="statusDropdownMenuLink{!! $row['id'] !!}">
                                                    <li class="nav-header border">{{ __('dropdown.choose_status') }}</li>
                                                    @foreach ($statusLabels as $key => $label)
                                                        <li class="dropdown-item">
                                                            <a
                                                                href="javascript:void(0);"
                                                                class="{!! $label['class'] !!}"
                                                                data-label="{{ $label['name'] }}"
                                                                data-value="{{ $row['id'] }}_{{ $key }}_{!! $label['class'] !!}"
                                                                id="ticketStatusChange{{ $row['id'] . $key }}"
                                                            >{{ $label['name'] }}</a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="maincontentinner">
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

            <div class="maincontentinner">
                <div class="pull-right">
                    @if ($login::userIsAtLeast($roles::$editor))
                        <a
                            href="javascript:void(0);"
                            onclick="leantime.commentsController.toggleCommentBoxes(0);jQuery('.noCommentsMessage').toggle();"
                            id="mainToggler"
                            class="btn btn-link action-link"
                            style="margin-top:-7px;"
                        ><span class="fa fa-plus"></span> {{ __('links.add_new_report') }}</a>
                    @endif
                </div>

                <h5 class="subtitle">{{ __('subtitles.project_updates') }}</h5>

                <form method="post" action="{{ BASE_URL }}/dashboard/show">
                    <input type="hidden" name="comment" value="1" />
                        @if ($login::userIsAtLeast($roles::$editor))
                            <div id="comment0" class="commentBox tw-hidden">
                                <label for="projectStatus tw-inline">{{ __('label.project_status_is') }}</label>

                                <select name="status" id="projectStatus" class="tw-ml-0 tw-mb-[10px]">
                                    <option value="green">{{ __('label.project_status_green') }}</option>
                                    <option value="yellow">{{ __('label.project_status_yellow') }}</option>
                                    <option value="red">{{ __('label.project_status_red') }}</option>
                                </select>

                                <div class="commentReply">
                                    <textarea rows="5" cols="50" class="tinymceSimple tw-w-full" name="text"></textarea>
                                    <input
                                        type="submit"
                                        value="{{ __('buttons.save') }}"
                                        name="comment"
                                        class="btn btn-primary btn-success tw-ml-0"
                                    />
                                    <a
                                        href="javascript:void(0);"
                                        onclick="leantime.commentsController.toggleCommentBoxes(-1);jQuery('.noCommentsMessage').toggle();"
                                        class="tw-leading-[50px]"
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
                                    <div class="readMore tw-hidden tw-mt-[20px]">
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
                                                    <div class="inlineDropDownContainer tw-float-right tw-ml-[10px]">
                                                        <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown">
                                                            <i class="fa fa-ellipsis-v"></i>
                                                        </a>

                                                        <ul class="dropdown-menu">
                                                            @if ($row['userId'] == session("userdata.id"));
                                                                <li>
                                                                    <a href="{!! $delUrlBase . $row['id'] !!}" class="deleteComment">
                                                                        <span class="fa fa-trash"></span> {{ __('links.delete') }}
                                                                    </a>
                                                                </li>
                                                            @endif

                                                            @isset($ticket->id)
                                                                <li>
                                                                    <a
                                                                        href="javascript:void(0);"
                                                                        onclick="leantime.ticketsController.addCommentTimesheetContent({!! $row['id'] !!}, {!! $ticket->id !!})"
                                                                    >{{ __('links.add_to_timesheets') }}</a>
                                                                </li>
                                                            @endif
                                                        </ul>
                                                    </div>
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
                                                ><span class="fa fa-reply"></span> {{ __('links.reply') }}
                                                </a>
                                            @endif
                                        </div>

                                        <div class="replies">
                                            @if ($row['replies'])
                                                @foreach ($row['replies'] as $comment)
                                                    <x-comments::reply :comment="$comment" :iteration="$loop->iteration" />
                                                @endforeach
                                            @endif
                                            <x-comments::input :commentId="$row['id']" :user="session("userdata")" />
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            @if (count($comments) >= 3)
                                </div>
                            @endif
                        </div>

                    @if (count($comments) == 0)
                        <div style="padding-left:0px; clear:both;" class="noCommentsMessage">
                                {{ __('text.no_updates') }}
                        </div>
                    @endif
                    <div class="clearall"></div>
                </form>
                <div class="clearall"></div>
            </div>

            <div class="maincontentinner">
                <div class="row" id="projectProgressContainer">
                    <div class="col-md-12">
                        <h5 class="subtitle">{{ __('subtitles.project_progress') }}</h5>

                        <div id="canvas-holder" class="tw-w-full tw-h-[250px]">
                            <canvas id="chart-area"></canvas>
                        </div>

                        <br/><br/>
                    </div>
                </div>

                <div class="row" id="milestoneProgressContainer">
                    <div class="col-md-12">
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
                                         hx-get="<?=BASE_URL ?>/hx/tickets/milestones/showCard?milestoneId=<?=$row->id ?>">
                                        <div class="htmx-indicator">
                                                <?=$tpl->__("label.loading_milestone") ?>
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
</div>

@once @push('scripts')
<script type='text/javascript'>
    leantime.editorController.initSimpleEditor();
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
            leantime.authController.makeInputReadonly(".maincontentinner");
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
                        jQuery("#favoriteProject").find("i").removeClass("fa-solid").addClass("fa-regular");
                        jQuery("#favoriteProject").removeClass("isFavorite");
                    }
                );
            } else {
                leantime.reactionsController.addReactions(
                    'project',
                    {!! $project['id'] !!},
                    'favorite',
                    function() {
                        jQuery("#favoriteProject").find("i").removeClass("fa-regular").addClass("fa-solid");
                        jQuery("#favoriteProject").addClass("isFavorite");
                    }
                );
            }
        });

        leantime.ticketsController.initDueDateTimePickers();
        leantime.ticketsController.initDueDateTimePickers();

        @if($completedOnboarding === false)
            leantime.helperController.firstLoginModal();
        @endif

        @php(session(["userdata.settings.modals.projectDashboardTour" => 1]));
    });

    @dispatchEvent('scripts.beforeClose')
</script>
@endpush @endonce

@endsection
