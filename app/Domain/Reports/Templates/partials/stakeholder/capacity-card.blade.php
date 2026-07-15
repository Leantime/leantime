{{--
    Renders a single capacity vs. demand card. Used at two levels:
      - top-level: one card per program (strategy scope) or per project (program scope)
      - nested:    inside an expanded program card, one compact card per child project

    Vars in:
      $c              The capacity analysis row (project or program-rolled).
      $verdictLabels  Map of verdict key → translated label.
      $compactOnly    When true, always render the one-liner regardless of verdict.
--}}

@php
    $compactOnly = $compactOnly ?? false;
    $vLabel = $verdictLabels[$c['verdict']] ?? $c['verdict'];
    $showFull = ! $compactOnly && in_array($c['verdict'], ['critical', 'tight', 'no_capacity'], true);
@endphp

@if ($showFull)
    @php
        // Balance bar geometry — carry the same reading the text states.
        //   Fill 0 → available in the "supply" (green) segment.
        //   Then a distinct "deficit" segment from available → needed, in
        //   the verdict color. Marker sits AT the available position so a
        //   reader instantly sees "we have this much, we need this much".
        $barMax = max($c['availableHours'], $c['referenceDemand'], 1);
        $availableMark = min(100, ($c['availableHours'] / $barMax) * 100);
        $demandWidth = min(100, ($c['referenceDemand'] / $barMax) * 100);
        $deficitWidth = max(0, $demandWidth - $availableMark);
        $gapHrs = abs($c['gap']);
        $gapPct = $c['availableHours'] > 0 ? abs($c['gap'] / $c['availableHours']) * 100 : 0;
        $isShort = $c['gap'] > 0;
    @endphp
    <div class="p3-cap {{ $c['verdict'] }}">
        <div class="p3-cap-hd">
            <span class="verdict {{ $c['verdict'] }}">
                <i class="fa fa-{{ $c['verdict'] === 'critical' ? 'triangle-exclamation' : ($c['verdict'] === 'no_capacity' ? 'ban' : 'circle-exclamation') }}"></i>
                {{ $vLabel }}
            </span>
            <div class="name">{{ $c['name'] }}</div>

            @if ($c['trustSignal'] === 'budgeted' && $c['effortHours'] > 0)
                <span class="trust good" data-tippy-content="{{ __('stakeholder.rc.cap.trust_budgeted') }}">
                    <i class="fa fa-check"></i>{{ __('stakeholder.rc.cap.trust_high') }}
                </span>
            @elseif ($c['trustSignal'] === 'effort')
                <span class="trust warn" data-tippy-content="{{ __('stakeholder.rc.cap.trust_effort') }}">
                    <i class="fa fa-triangle-exclamation"></i>{{ __('stakeholder.rc.cap.trust_effort_short') }}
                </span>
            @elseif ($c['trustSignal'] === 'mixed')
                <span class="trust warn" data-tippy-content="{{ sprintf(__('stakeholder.rc.cap.trust_mixed'), (int) ($c['divergence'] * 100)) }}">
                    <i class="fa fa-triangle-exclamation"></i>{{ __('stakeholder.rc.cap.trust_mixed_short') }}
                </span>
            @endif

            @if ($c['referenceDemand'] > 0 && $c['availableHours'] > 0)
                <div class="headline-num {{ $c['verdict'] }}">
                    @if ($isShort)
                        <span class="unit-h" data-hours="{{ round($gapHrs) }}">{{ round($gapHrs) }}h</span> {{ sprintf(__('stakeholder.rc.cap.short_suffix'), (int) $gapPct) }}
                    @else
                        <span class="unit-h" data-hours="{{ round($gapHrs) }}">{{ round($gapHrs) }}h</span> {{ __('stakeholder.rc.cap.buffer_suffix') }}
                    @endif
                </div>
            @endif
        </div>

        <div class="p3-cap-body">
            <div class="p3-cap-row">
                <div class="lbl">{{ __('stakeholder.rc.cap.scope') }}</div>
                <div class="val">
                    @if ($c['openTicketCount'] === 0)
                        <span class="muted">{{ __('stakeholder.rc.cap.no_tickets') }}</span>
                    @else
                        @if ($c['budgetedHours'] > 0)
                            <span class="primary"><span class="unit-h" data-hours="{{ round($c['budgetedHours']) }}">{{ round($c['budgetedHours']) }}h</span> {{ __('stakeholder.rc.cap.budgeted') }}</span>
                            <span class="muted">
                                ({{ $c['ticketsWithBudget'] }}/{{ $c['openTicketCount'] }}
                                {{ __('stakeholder.rc.cap.tickets_with_hours') }} — {{ (int) ($c['coverage'] * 100) }}% {{ __('stakeholder.rc.cap.coverage') }})
                            </span>
                        @else
                            <span class="muted">{{ __('stakeholder.rc.cap.no_budgeted') }} ({{ $c['openTicketCount'] }} {{ __('stakeholder.rc.cap.open_tickets') }})</span>
                        @endif
                        <br>
                        @if ($c['effortPoints'] > 0)
                            <span class="primary"><span class="unit-h" data-hours="{{ round($c['effortHours']) }}">{{ round($c['effortHours']) }}h</span> {{ __('stakeholder.rc.cap.effort') }}</span>
                            <span class="muted">({{ $c['effortPoints'] }}
                                <span class="pts-info" data-tippy-content="{{ __('stakeholder.rc.cap.points_help') }}">pts <i class="fa fa-circle-info"></i></span>
                                × {{ $c['hoursPerPoint'] }}h/pt)</span>
                        @else
                            <span class="muted">{{ __('stakeholder.rc.cap.no_effort') }}</span>
                        @endif
                    @endif
                </div>
            </div>

            <div class="p3-cap-row">
                <div class="lbl">{{ __('stakeholder.rc.cap.capacity') }}</div>
                <div class="val">
                    @if ($c['peopleCount'] === 0)
                        <span class="muted">{{ __('stakeholder.rc.cap.no_people') }}</span>
                    @else
                        <span class="primary"><span class="unit-h" data-hours="{{ round($c['availableHours']) }}">{{ round($c['availableHours']) }}h</span> {{ __('stakeholder.rc.cap.available_in_period') }}</span>
                        <span class="muted">
                            ({{ $c['peopleCount'] }} {{ __('stakeholder.rc.cap.people') }} × <span class="unit-h" data-hours="{{ round($c['weeklyHoursToProject'] / max(1, $c['peopleCount']), 1) }}">{{ round($c['weeklyHoursToProject'] / max(1, $c['peopleCount']), 1) }}h</span>/wk × {{ $c['weeksInWindow'] }} {{ __('stakeholder.rc.cap.weeks') }})
                        </span>
                    @endif
                </div>
            </div>

            @if ($c['referenceDemand'] > 0 && $c['availableHours'] > 0)
                <div class="p3-cap-row">
                    <div class="lbl">{{ __('stakeholder.rc.cap.balance') }}</div>
                    <div class="val">
                        <span class="unit-h" data-hours="{{ round($c['referenceDemand']) }}">{{ round($c['referenceDemand']) }}h</span> {{ __('stakeholder.rc.cap.demand') }}
                        <span class="divider">·</span>
                        <span class="unit-h" data-hours="{{ round($c['availableHours']) }}">{{ round($c['availableHours']) }}h</span> {{ __('stakeholder.rc.cap.supply') }}
                        <div class="p3-cap-bar">
                            <div class="track {{ $c['verdict'] }}">
                                {{-- Supply segment: green fill 0 → available. --}}
                                <div class="supply" style="width:{{ $availableMark }}%;"></div>
                                {{-- Deficit segment: verdict-color band from
                                     available → needed, showing the shortfall. --}}
                                @if ($isShort && $deficitWidth > 0)
                                    <div class="deficit" style="left:{{ $availableMark }}%;width:{{ $deficitWidth }}%;"
                                        data-tippy-content="{{ sprintf(__('stakeholder.rc.cap.deficit_tooltip'), round($gapHrs)) }}"></div>
                                @endif
                                <div class="marker" style="left:{{ $availableMark }}%;">
                                    <span class="marker-label">{{ __('stakeholder.rc.cap.marker_label') }}</span>
                                </div>
                            </div>
                            <div class="legend">
                                <span><span class="unit-h" data-hours="0">0h</span></span>
                                <span><span class="unit-h" data-hours="{{ round($barMax) }}">{{ round($barMax) }}h</span></span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if ($isShort && $c['recommendations']['extendWeeks'] > 0)
                @php $r = $c['recommendations']; @endphp
                <div class="p3-cap-rebalance">
                    <div class="hd">{{ __('stakeholder.rc.cap.rebalance_hd') }}</div>
                    <div class="opts">
                        <div class="opt">
                            <div class="icn"><i class="fa fa-calendar-plus"></i></div>
                            <div class="lever">{{ __('stakeholder.rc.cap.lever_extend_pre') }} <b>{{ $r['extendWeeks'] }}</b> {{ __('stakeholder.rc.cap.lever_extend_post') }}</div>
                            <div class="detail">{{ sprintf(__('stakeholder.rc.cap.lever_extend_detail'), round($c['weeklyHoursToProject'])) }}</div>
                        </div>
                        <div class="opt">
                            <div class="icn"><i class="fa fa-user-plus"></i></div>
                            <div class="lever">{{ __('stakeholder.rc.cap.lever_add_pre') }} <b>{{ $r['addPeople'] }}</b> {{ $r['addPeople'] === 1 ? __('stakeholder.rc.cap.lever_add_post_one') : __('stakeholder.rc.cap.lever_add_post_many') }}</div>
                            <div class="detail">{{ sprintf(__('stakeholder.rc.cap.lever_add_detail'), round($c['weeklyHoursToProject'] / max(1, $c['peopleCount']), 1), $c['weeksInWindow']) }}</div>
                        </div>
                        <div class="opt">
                            <div class="icn"><i class="fa fa-scissors"></i></div>
                            <div class="lever">{{ __('stakeholder.rc.cap.lever_cut_pre') }} <b>{{ round($r['cutPoints']) }}</b> {{ __('stakeholder.rc.cap.lever_cut_post') }}</div>
                            <div class="detail">{{ sprintf(__('stakeholder.rc.cap.lever_cut_detail'), round($r['cutHours']), $c['hoursPerPoint']) }}</div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@else
    {{-- Compact one-liner --}}
    <div class="p3-cap-compact">
        <span class="verdict {{ $c['verdict'] }}">
            @if ($c['verdict'] === 'buffer')<i class="fa fa-check"></i>@else<i class="fa fa-minus"></i>@endif
            {{ $vLabel }}
        </span>
        <div class="name">{{ $c['name'] }}</div>
        <div class="summary">
            @if ($c['verdict'] === 'no_work')
                {{ __('stakeholder.rc.cap.summary_no_work') }}
            @else
                <span class="unit-h" data-hours="{{ round($c['referenceDemand']) }}">{{ round($c['referenceDemand']) }}h</span> {{ __('stakeholder.rc.cap.summary_needed') }}
                ·
                <span class="unit-h" data-hours="{{ round($c['availableHours']) }}">{{ round($c['availableHours']) }}h</span> {{ __('stakeholder.rc.cap.summary_available') }}
            @endif
        </div>
    </div>
@endif
