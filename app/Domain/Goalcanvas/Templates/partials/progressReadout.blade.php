{{--
    Goal metric readout — RA planbar language: filled progress + striped
    remaining, quarter score marks, a position marker at the current value,
    start/goal anchored under the bar's ends, and an explicit "N to go".
    The number IS the input (RA inline pattern): blur-when-changed hx-posts
    to goalProgress/updateValue, which re-renders this partial into #gvReadout.

    Expects: $canvasItem (id, metricType, startValue, currentValue, endValue,
    setting, description). Shared by the dialog and the HxController.
--}}
@php
    $roType = $canvasItem['metricType'] ?: 'number';
    $roStart = (float) ($canvasItem['startValue'] ?? 0);
    $roCur = (float) ($canvasItem['currentValue'] ?? 0);
    $roGoal = (float) ($canvasItem['endValue'] ?? 0);
    $roHasRange = ($roGoal - $roStart) != 0;
    $roPct = $roHasRange ? max(0, min(100, (($roCur - $roStart) / ($roGoal - $roStart)) * 100)) : 0;
    $roFmt = function ($v) use ($roType) {
        $v = (float) $v;
        if ($roType === 'percent') {
            return rtrim(rtrim(number_format($v, 2), '0'), '.').'%';
        }
        if ($roType === 'currency') {
            return '$'.number_format($v, $v == floor($v) ? 0 : 2);
        }

        return rtrim(rtrim(number_format($v, 2), '0'), '.');
    };
    $roReached = $roHasRange && $roPct >= 100;
    // Works for decreasing goals too (goal < start): distance left to travel.
    $roRemaining = abs($roGoal - $roCur);
    // linkAndReport current values are computed from children; viewers can't
    // write — both render the number as static text instead of an input.
    $roEditable = $login::userIsAtLeast($roles::$editor) && ($canvasItem['setting'] ?? '') !== 'linkAndReport';
@endphp
<div class="gv-metric-bar" id="gvReadout">
    {{-- WHAT is being measured — without it the tab is a bare number. --}}
    @if (trim((string) ($canvasItem['description'] ?? '')) !== '')
        <div class="gv-mb-metric">{{ $canvasItem['description'] }}</div>
    @endif
    <div class="gv-mb-top">
        @if ($roEditable)
            <input class="gv-mb-input" type="number" step="0.01" name="currentValue"
                   value="{{ $roCur == floor($roCur) ? (int) $roCur : $roCur }}"
                   aria-label="{{ __('goalcanvas.update_current') }}"
                   data-tippy-content="{{ __('goalcanvas.update_current') }}"
                   hx-post="{{ BASE_URL }}/hx/goalcanvas/goalProgress/updateValue"
                   hx-vals='{"itemId": {{ (int) $canvasItem['id'] }}}'
                   hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'
                   hx-trigger="blur changed"
                   hx-target="#gvReadout"
                   hx-swap="outerHTML">
        @else
            <span class="gv-mb-now" @if (($canvasItem['setting'] ?? '') === 'linkAndReport') data-tippy-content="{{ __('text.current_value_calculated_from_children') }}" @endif>{{ $roFmt($roCur) }}</span>
        @endif
        {{-- How far away it ISN'T — the missing half of every progress bar. --}}
        @if ($roHasRange)
            <span class="gv-mb-togo">
                @if ($roReached)
                    {{ __('goalcanvas.goal_reached') }}
                @else
                    {{ sprintf(__('goalcanvas.to_go'), $roFmt($roRemaining)) }}
                @endif
            </span>
        @endif
    </div>
    {{-- Scored track (RA planbar language) + resolved % — the % column
         width/gap matches the milestone rows so both right edges align. --}}
    <div class="gv-mb-barrow">
        <div class="gv-mb-scalewrap">
            <div class="gv-track gv-track--scored" aria-hidden="true">
                <div class="gv-fill" style="width:{{ $roPct }}%"></div>
                <span class="gv-tick" style="left:25%"></span>
                <span class="gv-tick" style="left:50%"></span>
                <span class="gv-tick" style="left:75%"></span>
                @if ($roPct > 0 && $roPct < 100)
                    <span class="gv-marker" style="left:{{ $roPct }}%"></span>
                @endif
            </div>
            {{-- The totals live where they ARE on the scale: start under the
                 left end, the goal under the right end. --}}
            <div class="gv-scale"><span>{{ $roFmt($roStart) }}</span><span>{{ $roFmt($roGoal) }}</span></div>
        </div>
        <span class="gv-mb-pct">{{ (int) round($roPct) }}%</span>
    </div>
</div>
