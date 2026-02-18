@props([
    'includeTitle' => true
])

@if ($includeTitle)
    <h5 class="subtitle">Project Checklist <i class="fa fa-question-circle-o helperTooltip" data-tippy-content="The project checklist is list of activities you should do to ensure your projects are well defined, planned and executed."></i> </h5><br/>

@endif

<form name="progressForm" id="progressForm">
    <div class="projectSteps">
        <div class="progressWrapper">
            <div class="progress">
                <div
                    id="progressChecklistBar"
                    class="progress-bar progress-bar-success tx-transition"
                    role="progressbar"
                    aria-valuenow="0"
                    aria-valuemin="0"
                    aria-valuemax="100"
                    style="width: {{ $percentDone }}%"
                ><span class="sr-only">{{ $percentDone }}%</span></div>
            </div>

            @foreach ($progressSteps as $step)
                <div class="step {{ $step['stepType'] }}" style="left: {{ $step['positionLeft'] }}%;">
                    <a href="javascript:void(0)" class="dropdown-toggle" data-tippy-content="{{ __($step['description']) }}">
                        <span class="innerCircle"></span>
                        <span class="title">
                            @if ($step['status'] == 'done')
                                <i class="fa fa-circle-check"></i>
                            @else
                                <i class="fa-regular fa-circle"></i>
                            @endif
                                {{ __("text.step_".$loop->index + 1) }}: {{ __($step['title']) }}
                            <i class="fa fa-caret-down" aria-hidden="true"></i>
                        </span>
                    </a>
                    <ul class="dropdown-menu">

                        @foreach ($step['tasks'] as $key => $task)
                            <li @if ($task['status'] == 'done') class="done" @endif>
                                <input
                                    type="checkbox"
                                    name="{{ $key }}"
                                    id="progress_{{ $key }}"
                                    hx-patch="{{ BASE_URL }}/hx/projects/checklist/update-subtask/"
                                    hx-target="#progressForm"
                                    hx-swap="outerHTML"
                                    @if ($task['status'] == 'done') checked @endif
                                    @if (! in_array($step['stepType'], ['complete', 'current']))
                                        disabled

                                    @endif
                                />
                                <label for="progress_{{ $key }}"
                                       @if (! in_array($step['stepType'], ['complete', 'current']))
                                           data-tippy-content="Finish the previous steps first"

                                       @endif

                                >{{ __($task['title'] ?? '') }}</label>
                                <span class="clearall"></span>
                                <span class="taskDescription">
                                {{ __($task['description'] ?? '') }}<br />
                                <a href="{{ $task['link'] ?? '#' }}"><i class="fa fa-external-link"></i> Take me there</a>
                                </span>


                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
</form>
