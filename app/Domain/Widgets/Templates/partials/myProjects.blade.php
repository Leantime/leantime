@props([
    'includeTitle' => true,
    'allProjects' => []
])

<div class="maincontentinner">
    <a href="{{ BASE_URL }}/projects/showMy" class="pull-right">{{ __('links.my_portfolio') }}</a>
    <h5 class="subtitle">{{ __("headline.your_projects") }}</h5>
    <br/>
    <div class='col-md-12'>
        @if (count($allProjects) == 0)
                <br /><br />
                <div class='center'>
                    <div style='width:70%' class='svgContainer'>
                        {{ __('notifications.not_assigned_to_any_project') }}
                        @if($login::userIsAtLeast($roles::$manager))
                            <br /><br />
                            <a href='{{ BASE_URL }}/projects/newProject' class='btn btn-primary'>{{ __('link.new_project') }}</a>
                        @endif
                    </div>
                </div>
        @endif
        <ul class="sortableTicketList" id="projectProgressContainer">
        @foreach ($allProjects as $project) {
            @php( $percentDone = round($project['progress']['percent']))

            <li>
                <div class="col-md-12">

                    <div class="row" >
                        <div class="col-md-12 ticketBox fixed">

                            <div class="row" style="padding-bottom:10px;">

                                <div class="col-md-8">
                                    <a href="{{ BASE_URL }}dashboard/show?projectId={{ $project['id'] }}">
                                                            <span class="projectAvatar">
                                                                <img src="{{ BASE_URL }}/api/projects?projectAvatar={{ $project['id'] }}" />
                                                            </span>
                                        <small>{{ $project['clientName'] }}</small><br />
                                        <strong>{{ $project['name'] }}</strong>
                                    </a>
                                </div>
                                <div class="col-md-4" style="text-align:right">
                                    @if ($project['status'] !== null && $project['status'] != '')
                                        <span class="label label-{{ $project['status'] }}">
                                            {{ __("label.project_status_" . $project['status'] }}
                                        </span><br />

                                    @else
                                        <span class="label label-grey">
                                            {{ __("label.no_status") }}
                                        </span><br />
                                    @endif

                                </div>
                            </div>
                            <div class="row">

                                <div class="col-md-7">
                                        {{ __("subtitles.project_progress") }}
                                </div>
                                <div class="col-md-5" style="text-align:right">
                                        {{ sprintf(__("text.percent_complete"), round($percentDone)) }}
                                </div>
                            </div>
                            <div class="progress">
                                <div class="progress-bar progress-bar-success"
                                     role="progressbar"
                                     aria-valuenow="{{ $percentDone }}"
                                     aria-valuemin="0"
                                     aria-valuemax="100"
                                     style="width: {{ $percentDone }}%">
                                        <span class="sr-only">{{ sprintf(__("text.percent_complete"), $percentDone }}</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
        @endforeach
    </ul>
    </div>
</div>

@dispatchEvent('afterMyProjectBox')
