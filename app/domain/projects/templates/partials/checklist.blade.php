@props([
    'includeTitle' => true
])

@if ($includeTitle)
    <strong>Project Checklist</strong><br/><br/>
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
                    <a href="javascript:void(0)" data-toggle="dropdown" class="dropdown-toggle">
                        <span class="innerCircle"></span>
                        <span class="title">
                            @if ($step['status'] == 'done')
                                <i class="fa fa-check"></i>
                            @endif
                            {{ __($step['title']) }}
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
                                    hx-patch="/hx/projects/checklist/update-subtask/"
                                    hx-target="#progressForm"
                                    hx-swap="outerHTML"
                                    @if ($task['status'] == 'done') checked @endif
                                    @if (! in_array($step['stepType'], ['complete', 'current']))
                                        disabled
                                    @endif
                                />
                                <label for="progress_{{ $key }}">{{ __($task['title'] ?? '') }}</label>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
</form>
