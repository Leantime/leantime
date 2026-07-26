{{--
    Commitment integrity strip: milestones whose due date was pushed out of the period, and
    milestones added mid-period. Kept compact — it's an honesty note, not a section.

    Expects:
    $slippage:     array{pushedOut: object[], addedMidPeriod: object[]}
    $showProjects: bool
--}}
@php
    $showProjects = $showProjects ?? false;
@endphp

@if (!empty($slippage['pushedOut']) || !empty($slippage['addedMidPeriod']))
    <div class="reportSlippage">
        <strong class="tw-block tw-mb-1"><i class="fa fa-fw fa-arrows-left-right tw-opacity-60"></i> {{ __('subtitles.changed_this_period') }}</strong>

        @foreach ($slippage['pushedOut'] as $milestone)
            <div>
                <strong>{{ $tpl->escape($milestone->headline) }}</strong>
                @if ($showProjects)<span class="tw-opacity-70">({{ $tpl->escape($milestone->projectName) }})</span>@endif
                {{ sprintf(__('text.slippage_moved_out'), $milestone->dueDateMoves, $milestone->dueDate?->formatDateForUser() ?? '—') }}
            </div>
        @endforeach

        @foreach ($slippage['addedMidPeriod'] as $milestone)
            <div>
                <strong>{{ $tpl->escape($milestone->headline) }}</strong>
                @if ($showProjects)<span class="tw-opacity-70">({{ $tpl->escape($milestone->projectName) }})</span>@endif
                {{ __('text.slippage_added_mid_period') }}
            </div>
        @endforeach
    </div>
@endif
