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
                        <x-tickets::ticket-card :id="$row['id']" />
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
                <x-dashboard::project-updates :id="$project['id']" />

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
                            <x-tickets::milestone-card :milestone="$row" />
                        @endforeach

                    </div>
            </x-global::content.card>
        </div>

    </div>
</div>
</div>

@once @push('scripts')
<script>
    @dispatchEvent('scripts.afterOpen')

    document.body.addEventListener("htmx:responseError", function(event) {
        const loader = event.target.querySelector('#htmx-loader');
        const errorElement = event.target.querySelector('.error-message');
        if (loader) {
            loader.style.display = 'none'; // Hide the loader
        }
        if (errorElement) {
            errorElement.style.display = 'block'; // Show the error message
        }
    });

    document.body.addEventListener("htmx:beforeRequest", function(event) {
        const loader = event.target.querySelector('#htmx-loader');
        const errorElement = event.target.querySelector('.error-message');
        if (loader) {
            loader.style.display = 'flex'; // Show the loader
        }
        if (errorElement) {
            errorElement.style.display = 'none'; // Hide the error message
        }
    });


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
