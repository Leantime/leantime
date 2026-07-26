{{--
    Status narrative: the period's status updates as a human-readable feed, grouped by
    project, newest first, colored by their green/yellow/red status.

    Expects:
    $updatesByProject: array<int, object[]> - projectId => status updates (engine output)
    $summaries:        array<int, object> - project summaries keyed by id (for names)
    $showProjects:     bool
    $emptyText:        string
--}}
@php
    $showProjects = $showProjects ?? false;
    $narrativeColors = ['green' => 'var(--green)', 'yellow' => 'var(--yellow)', 'red' => 'var(--red)'];
@endphp

@if (count($updatesByProject) === 0)
    <div class="tw-opacity-60 tw-text-sm tw-py-2">{{ $emptyText }}</div>
@else
    <div class="reportStatusNarrative tw-flex tw-flex-col tw-gap-3">
        @foreach ($updatesByProject as $projectId => $updates)
            @if ($showProjects && isset($summaries[$projectId]))
                <strong class="tw-mt-1">{{ $tpl->escape($summaries[$projectId]->name) }}</strong>
            @endif
            @foreach ($updates as $update)
                <div class="reportStatusUpdate tw-pl-3 tw-py-1" style="border-left: 4px solid {{ $narrativeColors[$update->status] ?? 'var(--grey)' }};">
                    <div class="tw-text-xs tw-opacity-60">
                        {{ $tpl->escape(trim(($update->authorFirstname ?? '').' '.($update->authorLastname ?? ''))) }}
                        · {{ $update->dateParsed?->formatDateForUser() ?? '' }}
                    </div>
                    <div class="tw-text-sm">{!! $tpl->escapeMinimal($update->text) !!}</div>
                </div>
            @endforeach
        @endforeach
    </div>
@endif
