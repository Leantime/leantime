@extends($layout)

@section('content')
    <x-global::content.pageheader :icon="'fa fa-gauge-high'">
        @if (count($allUsers) == 1)
            <a href="#/users/newUser" class="headerCTA">
                <i class="fa fa-users"></i>
                {{ __('links.dont_do_it_alone') }}

            </a>
        @endif

        <h5>{{ session('currentProjectClient') }}</h5>
        <h1>{!! __('headlines.project_dashboard') !!}</h1>
    </x-global::content.pageheader>

    <div class="maincontent">

        <div class="row">

            <div class="col-md-8">

                <x-global::content.card variation="content">
                    <x-slot:card-context-buttons>

                        <x-global::forms.button shape="circle" content-role="tertiary"
                            data-tippy-content="{{ __('label.favorite_tooltip') }}"
                            onclick="leantime.snippets.copyToClipboard('{{ BASE_URL }}/project/changeCurrentProject/{{ $project['id'] }}')"
                            class="{{ $isFavorite ? 'btn-active' : '' }}">
                            <i class="{{ $isFavorite ? 'fa-solid' : 'fa-regular' }} fa-star"></i>
                        </x-global::forms.button>

                        <x-global::forms.button shape="circle" content-role="tertiary"
                            data-tippy-content="{{ __('label.copy_url_tooltip') }}"
                            onclick="leantime.snippets.copyToClipboard('{{ BASE_URL }}/project/changeCurrentProject/{{ $project['id'] }}')">
                            <i class='fa fa-link'></i>
                        </x-global::forms.button>

                        @if ($login::userIsAtLeast($roles::$admin))
                            <x-global::actions.dropdown content-role="tertiary" position="bottom" align="start"
                                class="" button-shape="circle" data-tippy-content="{{ __('label.edit_project') }}"
                                href="{{ BASE_URL }}/projects/showProject/{{ $project['id'] }}">
                                <x-slot:label-text>
                                    <i class='fa fa-ellipsis-v'></i>
                                </x-slot:label-text>

                                <x-slot:menu>
                                    <!-- Edit Project Menu Item -->
                                    <x-global::actions.dropdown.item variant="link"
                                        href="{{ BASE_URL }}/projects/showProject/{{ $project['id'] }}">
                                        <i class="fa fa-edit"></i> Edit Project
                                    </x-global::actions.dropdown.item>

                                    <!-- Delete Project Menu Item -->
                                    <x-global::actions.dropdown.item variant="link"
                                        href="{{ BASE_URL }}/projects/delProject/{{ $project['id'] }}"
                                        class="delete">
                                        <i class="fa fa-trash"></i> Delete Project
                                    </x-global::actions.dropdown.item>
                                </x-slot:menu>

                            </x-global::actions.dropdown>

                        @endif

                    </x-slot:card-context-buttons>

                    <x-slot:card-title>{{ $currentProjectName }}</x-slot:card-title>

                    @include('projects::includes.checklist', [
                        'progressSteps' => $progressSteps,
                        'percentDone' => $percentDone,
                    ])

                    <br /><br />
                    <article class="prose">
                        <strong>{{ __('label.background') }}</strong><br />
                        <div class="readMoreBox">
                            <div class="mce-content-body kanbanContent closed max-h-[200px] readMoreContent pb-[30px]"
                                id="projectDescription">
                                {!! $tpl->escapeMinimal($project['details']) !!}
                            </div>

                            <div class="center readMoreToggle" style="display:none;">
                                <a href="javascript:void(0)" id="descriptionReadMoreToggle">{{ __('label.read_more') }}</a>
                            </div>
                        </div>
                    </article>
                </x-global::content.card>

                <x-global::content.card variation="content">

                    <x-slot:card-title>{{ __('headlines.latest_todos') }}</x-slot:card-title>

                    @if (count($tickets) == 0)
                        <em>Nothing to see here. Move on.</em><br /><br />
                    @endif

                    @foreach ($tickets as $row)
                        <x-tickets::ticket-card
                            :id="$row['id']"
                        />
                    @endforeach

                </x-global::content.card>

                <x-global::content.card variation="content">
                    <x-slot:card-title>{{ __('tabs.team') }}</x-slot:card-title>
                    @dispatchEvent('teamBoxBeginning', ['project' => $project])

                    <div class="row teamBox">
                        @foreach ($project['assignedUsers'] as $userId => $assignedUser)
                            <div class="col-md-3">
                                <x-users::profile-box :user="$assignedUser">
                                    @spaceless
                                        @php $hasName = $assignedUser['firstname'] != '' || $assignedUser['lastname'] != ''; @endphp

                                        @if ($hasName)
                                            {{ sprintf(__('text.full_name'), $assignedUser['firstname'], $assignedUser['lastname']) }}
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
                                    </a><br />&nbsp;
                                </x-users::profile-box>
                            </div>
                        @endif
                    </div>
                </x-global::content.card>
            </div>

            <div class="col-md-4">

                <x-global::content.card variation="content">
                    <x-slot:card-context-buttons>
                        @if ($login::userIsAtLeast($roles::$editor))
                            <x-global::forms.button content-role="ghost"
                                data-tippy-content="{{ __('label.copy_url_tooltip') }}"
                                onclick="leantime.commentsController.toggleCommentBoxes(0);jQuery('.noCommentsMessage').toggle();">
                                <i class="fa fa-plus"></i> {{ __('links.add_new_report') }}
                            </x-global::forms.button>
                        @endif
                    </x-slot:card-context-buttons>

                    <x-slot:card-title>{{ __('subtitles.project_updates') }}</x-slot:card-title>

                    <form method="post" action="{{ BASE_URL }}/dashboard/show">
                        <input type="hidden" name="comment" value="1" />
                        @if ($login::userIsAtLeast($roles::$editor))
                            <div id="comment0" class="commentBox hidden">
                                <x-global::forms.select name="status" id="projectStatus" class="ml-0 mb-[10px]"
                                    :labelText="__('label.project_status_is')">
                                    <x-global::forms.select.select-option value="green">
                                        {{ __('label.project_status_green') }}
                                    </x-global::forms.select.select-option>
                                    <x-global::forms.select.select-option value="yellow">
                                        {{ __('label.project_status_yellow') }}
                                    </x-global::forms.select.select-option>
                                    <x-global::forms.select.select-option value="red">
                                        {{ __('label.project_status_red') }}
                                    </x-global::forms.select.select-option>
                                </x-global::forms.select>


                                <div class="commentReply">
                                    <textarea rows="5" cols="50" class="tinymceSimple w-full" name="text"></textarea>
                                    <x-global::forms.button type="submit" name="comment" class="btn-success ml-0">
                                        {{ __('buttons.save') }}
                                    </x-global::forms.button>

                                    <x-global::forms.button tag="a" href="javascript:void(0);" onclick="leantime.commentsController.toggleCommentBoxes(-1); jQuery('.noCommentsMessage').toggle();" content-role="secondary" class="leading-[50px]">
                                        {{ __('links.cancel') }}
                                    </x-global::forms.button>

                                    <input type="hidden" name="comment" value="1" />
                                    <input type="hidden" name="father" id="father" value="0" />
                                </div>
                            </div>
                        @endif

                        <div id="comments">
                            @foreach ($comments as $row)
                                @if ($loop->iteration == 3)
                                    <a href="javascript:void(0);" onclick="jQuery('.readMore').toggle('fast')">
                                        {{ __('links.read_more') }}
                                    </a>
                                    <div class="readMore hidden mt-[20px]">
                                @endif
                                <div class="clearall">
                                    <div>
                                        <div class="commentContent statusUpdate commentStatus-{{ $row['status'] }}">
                                            <strong class="fancyLink">
                                                {{ sprintf(__('text.report_written_on'), format($row['date'])->date(), format($row['date'])->time()) }}
                                            </strong>
                                            @if ($login::userIsAtLeast($roles::$editor))
                                                <div class="inlineDropDownContainer float-right ml-[10px]">
                                                    <a href="javascript:void(0)" class="dropdown-toggle"
                                                        data-toggle="dropdown">
                                                        <i class="fa fa-ellipsis-v"></i>
                                                    </a>

                                                    <ul class="dropdown-menu">
                                                        @if ($row['userId'] == session('userdata.id'));
                                                            <li>
                                                                <a href="{!! $delUrlBase . $row['id'] !!}" class="deleteComment">
                                                                    <span class="fa fa-trash"></span>
                                                                    {{ __('links.delete') }}
                                                                </a>
                                                            </li>
                                                        @endif

                                                        @isset($ticket->id)
                                                            <li>
                                                                <a href="javascript:void(0);"
                                                                    onclick="leantime.ticketsController.addCommentTimesheetContent({!! $row['id'] !!}, {!! $ticket->id !!})">{{ __('links.add_to_timesheets') }}</a>
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
                                        $tpl->escape($row['lastname']),
                                    ) !!}
                                </small>

                                @if ($login::userIsAtLeast($roles::$commenter))
                                    <a href="javascript:void(0);"
                                        onclick="leantime.commentsController.toggleCommentBoxes({!! $row['id'] !!});"><span
                                            class="fa fa-reply"></span> {{ __('links.reply') }}
                                    </a>
                                @endif
                            </div>

                            <div class="replies">
                                @if ($row['replies'])
                                    @foreach ($row['replies'] as $comment)
                                        <x-comments::reply :comment="$comment" :iteration="$loop->iteration" />
                                    @endforeach
                                @endif
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

        </x-global::content.card>

        <x-global::content.card variation="content">
            <x-slot:card-title>
                {{ __('subtitles.project_progress') }}
            </x-slot:card-title>

            <div class="flex flex-row justify-center items-center">
                <div class="radial-progress before:drop-shadow-[0_0px_15px_rgba(0,0,0,0.5)] text-primary bg-base-100/50 border-base-300 border-4 align-center shadow-md"
                    style="--value:70;  --size:10rem; --thickness: 1rem;" role="progressbar">
                    {{ round($projectProgress['percent']) }}%
                </div>
            </div>


                    <div class="row" id="milestoneProgressContainer">
                        <div class="col-md-12">
                            <h5 class="subtitle">{{ __('headline.milestones') }}</h5>

                            @if (count($milestones) == 0)
                                <div class="center">
                                    <br />
                                    <h4>{{ __('headlines.no_milestones') }}</h4>
                                    {{ __('text.milestones_help_organize_projects') }}
                                    <br /><br />
                                    <a href="{{ BASE_URL }}/tickets/roadmap">{!! __('links.goto_milestones') !!}</a>
                                </div>
                            @endif

                            @foreach ($milestones as $row)
                                @if ($row->percentDone >= 100 && new \DateTime($row->editTo) < new \DateTime())
                                    @break
                                @endif
                                <x-tickets::milestone-card 
                                    :milestone="$row"
                                />
                            @endforeach

                        </div>
                    </div>
                </x-global::content.card>

    </div>
    </div>
    </div>

    @once @push('scripts')
    <script>
        @dispatchEvent('scripts.afterOpen')

        leantime.editorController.initSimpleEditor();

        jQuery(document).ready(function() {

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

            jQuery(document).on('click', '.progressWrapper .dropdown-menu', function(e) {
                e.stopPropagation();
            });

            @if ($login::userIsAtLeast($roles::$editor))
                leantime.dashboardController.prepareHiddenDueDate();
                leantime.ticketsController.initEffortDropdown();
                leantime.ticketsController.initMilestoneDropdown();
                leantime.ticketsController.initStatusDropdown();
            @else
                leantime.authController.makeInputReadonly(".maincontentinner");
            @endif

            jQuery("#favoriteProject").click(function() {
                if (jQuery("#favoriteProject").hasClass("isFavorite")) {
                    leantime.reactionsController.removeReaction(
                        'project',
                        {!! $project['id'] !!},
                        'favorite',
                        function() {
                            jQuery("#favoriteProject").find("i").removeClass("fa-solid").addClass(
                                "fa-regular");
                            jQuery("#favoriteProject").removeClass("isFavorite");
                        }
                    );
                } else {
                    leantime.reactionsController.addReactions(
                        'project',
                        {!! $project['id'] !!},
                        'favorite',
                        function() {
                            jQuery("#favoriteProject").find("i").removeClass("fa-regular").addClass(
                                "fa-solid");
                            jQuery("#favoriteProject").addClass("isFavorite");
                        }
                    );
                }
            });

            leantime.ticketsController.initDueDateTimePickers();
            leantime.ticketsController.initDueDateTimePickers();

            @if ($completedOnboarding === false)
                leantime.helperController.firstLoginModal();
            @endif

            @php(session(['usersettings.modals.projectDashboardTour' => 1]));
        });

        @dispatchEvent('scripts.beforeClose')
    </script>
    @endpush @endonce
@endsection
