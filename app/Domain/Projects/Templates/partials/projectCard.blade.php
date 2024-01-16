@props([
    'project' => [],
    'type' => 'simple'
])

@php( $percentDone = format($project['progress']['percent'])->decimal())

<div class="projectBox">
    <div class="row" >
        <div class="col-md-12 fixed">
            <div class="row tw-pb-sm">
                <div class="col-md-10">
                    <a href="{{ BASE_URL }}/dashboard/show?projectId={{ $project['id'] }}">
                        <span class="projectAvatar">
                            @if(isset($projectTypeAvatars[$project["type"]]) && $projectTypeAvatars[$project["type"]] != "avatar")
                                <span class="{{ $projectTypeAvatars[$project["type"]] }}"></span>
                            @else
                                <img src='{{ BASE_URL }}/api/projects?projectAvatar={{ $project["id"] }}&v={{  format($project['modified'])->timestamp() }}' />
                            @endif
                        </span>
                        @if($project["clientName"] != '')
                            <small>{{ $project["clientName"] }}</small><br />
                        @else
                            <small>{{ __('projectType.'.$project["type"] ?? 'project') }}</small><br />
                        @endif
                        <strong>{{ $project['name'] }} <i class="fa-solid fa-up-right-from-square"></i></strong>
                    </a>
                </div>
                <div class="col-md-2 tw-text-right">
                    <a  href="javascript:void(0);"
                        onclick="leantime.projectsController.favoriteProject({{ $project['id'] }}, this)"
                        class="favoriteClick favoriteStar pull-right margin-right {{ $project['isFavorite'] ? 'isFavorite' : ''}} tw-mr-[5px]"
                        data-tippy-content="{{ __('label.favorite_tooltip') }}">
                            <i class="{{ $project['isFavorite'] ? 'fa-solid' : 'fa-regular' }} fa-star"></i>
                    </a>
                </div>
            </div>

            @if($type != "simple")
                <div class="row">
                    <div class="col-md-7">
                        {{ __("subtitles.project_progress") }}
                    </div>
                    <div class="col-md-5" style="text-align:right">
                        {{ sprintf(__("text.percent_complete"), $percentDone) }}
                    </div>
                </div>


                    <div class="progress">
                        <div class="progress-bar progress-bar-success"
                             role="progressbar"
                             aria-valuenow="{{ $percentDone }}"
                             aria-valuemin="0"
                             aria-valuemax="100"
                             style="width: {{ $percentDone }}%">
                            <span class="sr-only">{{ sprintf(__("text.percent_complete"), $percentDone) }}</span>
                        </div>
                    </div>



                <div class="row">
                    <div class="col-md-12">
                        @if ($project['status'] !== null && $project['status'] != '')
                            <span class="label label-{{ $project['status'] }}">
                                {{ __("label.project_status_" . $project['status']) }}
                            </span><br />
                        @else
                            <span class="label label-grey">{{ __("label.no_status") }}</span><br />
                        @endif
                    </div>
                </div>
                <br />
                <div class="row">
                    <div class="col-md-12">


                        <div class="team">
                            @foreach ($project['team'] as $member)
                                <div class="commentImage" style="margin-right:-10px;">
                                    <img
                                        style=""
                                        src="{{  BASE_URL }}/api/users?profileImage={{ $member['id'] }}&v={{ format($member['modified'])->timestamp() }}" data-tippy-content="{{ $member['firstname'] . ' ' . $member['lastname'] }}" />
                                </div>
                            @endforeach
                        </div>
                        <div class="clearall"></div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
