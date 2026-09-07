{{--
    Reporting period selector — THE shared picker for every report.

    A single pill showing the active preset and its resolved range, opening a
    menu of quarter presets plus a custom range. Replaces the two designs that
    had drifted apart: the strategy/program decks' `.rd-picker` (this design,
    but scoped to `.rd-scope`) and the project reports' row of four separate
    `btn-secondary` pills with a loose text label beside them.

    Props:
      period  ReportPeriod  REQUIRED. The active period.
      url     string        REQUIRED. Page URL — preset links, the custom-range
                            form action, and the pushed browser URL.
      hxUrl   string|null   Optional HTMX endpoint rendering the report body.
                            When given, presets swap the body in place and fall
                            back to a full page load without JS. When null the
                            presets are plain links (the deck's behavior).
      target  string        CSS selector of the body element to swap (hxUrl only).
      hints   array         Optional per-preset caption keyed by preset value,
                            e.g. [ReportPeriod::PRESET_LAST_QUARTER => 'how did we do?'].
--}}
@props([
    'period',
    'url',
    'hxUrl' => null,
    'target' => '#reportBody',
    'hints' => [],
])

@php
    use Leantime\Domain\Reports\Models\ReportPeriod;

    $presets = [
        ReportPeriod::PRESET_LAST_QUARTER => __('label.period_last_quarter'),
        ReportPeriod::PRESET_THIS_QUARTER => __('label.period_this_quarter'),
        ReportPeriod::PRESET_NEXT_QUARTER => __('label.period_next_quarter'),
    ];

    $isCustom = $period->preset === ReportPeriod::PRESET_CUSTOM;

    // The pill shows the preset NAME, never a calendar quarter label: Leantime
    // has no fiscal-quarter setting, so "Q3 2026" would be a lie for anyone
    // whose fiscal year isn't calendar-aligned. The literal range sits next to it.
    $activeName = $presets[$period->preset] ?? __('label.period_custom');

    // Unique id per instance so two pickers on one page can't toggle each other.
    $pickerId = 'ltPeriodPicker'.substr(md5($url.$period->preset), 0, 8);
@endphp

<div {{ $attributes->merge(['class' => 'lt-periodpicker']) }} id="{{ $pickerId }}" data-lt-periodpicker>
    <button type="button" class="lt-periodpicker-btn" aria-haspopup="true" aria-expanded="false">
        <i class="fa fa-calendar" aria-hidden="true"></i>
        <span class="lt-periodpicker-preset">{{ $activeName }}</span>
        <span class="lt-periodpicker-range">· {{ $period->from->setToUserTimezone()->format('M j') }} – {{ $period->to->setToUserTimezone()->format('M j, Y') }}</span>
        <i class="fa fa-caret-down" aria-hidden="true"></i>
    </button>

    <div class="lt-periodpicker-menu" hidden>
        @foreach ($presets as $presetKey => $presetLabel)
            <a href="{{ $url }}?preset={{ $presetKey }}"
               @if ($hxUrl)
                   hx-get="{{ $hxUrl }}?preset={{ $presetKey }}"
                   hx-target="{{ $target }}"
                   hx-swap="outerHTML"
                   hx-push-url="{{ $url }}?preset={{ $presetKey }}"
               @endif
               class="lt-periodpicker-opt @if ($period->preset === $presetKey) on @endif">
                <span class="l">{{ $presetLabel }}</span>
                @if (! empty($hints[$presetKey]))
                    <span class="d">{{ $hints[$presetKey] }}</span>
                @endif
            </a>
        @endforeach

        <div class="lt-periodpicker-sep"></div>

        {{-- Custom range always submits a plain GET: the two dates need to travel
             together, so there is nothing to swap until Apply is pressed. --}}
        <form method="GET" action="{{ $url }}" class="lt-periodpicker-custom">
            <input type="hidden" name="preset" value="{{ ReportPeriod::PRESET_CUSTOM }}" />
            <label class="lt-periodpicker-cl">{{ __('label.period_custom') }}</label>
            <div class="lt-periodpicker-crow">
                <input type="text" name="from" class="lt-periodpicker-input periodPickerDate"
                       placeholder="{{ __('label.period_from') }}"
                       value="{{ $isCustom ? $period->from->setToUserTimezone()->formatDateForUser() : '' }}" />
                <span class="lt-periodpicker-dash">–</span>
                <input type="text" name="to" class="lt-periodpicker-input periodPickerDate"
                       placeholder="{{ __('label.period_to') }}"
                       value="{{ $isCustom ? $period->to->setToUserTimezone()->formatDateForUser() : '' }}" />
                <button type="submit" class="lt-periodpicker-apply">{{ __('label.period_apply') }}</button>
            </div>
        </form>
    </div>
</div>

@once('lt-periodpicker-script')
    @push('scripts')
        <script>
            (function () {
                'use strict';

                function closeAll(except) {
                    document.querySelectorAll('[data-lt-periodpicker]').forEach(function (p) {
                        if (p === except) { return; }
                        var menu = p.querySelector('.lt-periodpicker-menu');
                        var trigger = p.querySelector('.lt-periodpicker-btn');
                        if (menu) { menu.setAttribute('hidden', ''); }
                        if (trigger) { trigger.setAttribute('aria-expanded', 'false'); }
                    });
                }

                function setOpen(picker, open) {
                    var menu = picker.querySelector('.lt-periodpicker-menu');
                    var trigger = picker.querySelector('.lt-periodpicker-btn');
                    if (menu) { menu.toggleAttribute('hidden', !open); }
                    if (trigger) { trigger.setAttribute('aria-expanded', open ? 'true' : 'false'); }
                }

                // Delegated, so pickers swapped in by HTMX work without re-init.
                document.addEventListener('click', function (e) {
                    var root = e.target.closest('[data-lt-periodpicker]');
                    var btn = e.target.closest('[data-lt-periodpicker] .lt-periodpicker-btn');

                    if (btn && root) {
                        var willOpen = root.querySelector('.lt-periodpicker-menu').hasAttribute('hidden');
                        closeAll(root);
                        setOpen(root, willOpen);
                        return;
                    }

                    // Picking a preset closes the menu — with hx-get the body swaps in
                    // place, so nothing else would dismiss it and it hung open over the
                    // report until the next outside click.
                    if (e.target.closest('.lt-periodpicker-opt')) {
                        closeAll();
                        if (root) { setOpen(root, false); }
                        return;
                    }

                    // Anywhere else inside an open menu (typing a range, picking a date
                    // in the calendar overlay): leave it alone.
                    if (e.target.closest('.lt-periodpicker-menu')) { return; }

                    closeAll();
                });

                document.addEventListener('keydown', function (e) {
                    if (e.key !== 'Escape') { return; }
                    // Reset aria-expanded too, or screen readers keep announcing the
                    // trigger as expanded after the menu is gone.
                    closeAll();
                });

                function initDatepickers() {
                    if (!window.jQuery || !jQuery.fn.datepicker) { return; }
                    jQuery('.lt-periodpicker-input').not('.hasDatepicker').datepicker({
                        dateFormat: window.leantime.dateHelper.getFormatFromSettings('dateformat', 'jquery'),
                    });
                }

                if (document.readyState !== 'loading') { initDatepickers(); }
                else { document.addEventListener('DOMContentLoaded', initDatepickers); }
                if (window.htmx) { window.htmx.onLoad(initDatepickers); }
            })();
        </script>
    @endpush
@endonce
