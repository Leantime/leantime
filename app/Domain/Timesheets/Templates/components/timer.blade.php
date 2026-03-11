@props([
    'parentTicketId' => false,
    'onTheClock'     => false,
    'variant'        => 'button',   // 'button' = inline icon, 'link' = dropdown text row
])

@php
    $isActive  = $onTheClock !== false && $onTheClock['id'] == $parentTicketId;
    $isOther   = $onTheClock !== false && $onTheClock['id'] != $parentTicketId;
    $isIdle    = $onTheClock === false;

    // Self-refresh: re-fetches this component on any timerUpdate event.
    // The variant is passed as a query param so the HxController returns
    // the correct template mode (link vs button) each time.
    $selfRefreshUrl = BASE_URL . '/hx/timesheets/timer/get-status/' . $parentTicketId . '?variant=' . $variant;

    $startUrl = BASE_URL . '/hx/timesheets/stopwatch/start-timer/';
    $stopUrl  = BASE_URL . '/hx/timesheets/stopwatch/stop-timer/';
@endphp

{{-- ── LINK VARIANT ────────────────────────────────────────────────────────────
     Used inside dropdown menus (ticket-submenu, ticket modal).
     Root element is <li> so it slots directly into <ul class="dropdown-menu">.
     ──────────────────────────────────────────────────────────────────────── --}}
@if($variant === 'link')

    <li id="timerContainer-{{ $parentTicketId }}"
        hx-get="{{ $selfRefreshUrl }}"
        hx-trigger="timerUpdate from:body"
        hx-target="this"
        hx-swap="outerHTML"
        aria-live="assertive"
        class="timerContainer">

        @if($isIdle)
            <a href="javascript:void(0);"
               hx-patch="{{ $startUrl }}"
               hx-target="#timerHeadMenu"
               hx-swap="outerHTML"
               hx-vals='{"ticketId": "{{ $parentTicketId }}", "action": "start"}'>
                <x-globals::elements.icon name="schedule" />
                {{ __('links.start_work') }}
            </a>

        @elseif($isActive)
            <a href="javascript:void(0);"
               hx-patch="{{ $stopUrl }}"
               hx-target="#timerHeadMenu"
               hx-swap="outerHTML"
               hx-vals='{"ticketId": "{{ $parentTicketId }}", "action": "stop"}'>
                <x-globals::elements.icon name="stop" />
                @if(is_array($onTheClock))
                    {!! sprintf(__('links.stop_work_started_at'), dtHelper()::createFromTimestamp($onTheClock['since'], 'UTC')->setToUserTimezone()->format(__('language.timeformat'))) !!}
                @else
                    {!! sprintf(__('links.stop_work_started_at'), dtHelper()::now()->setToUserTimezone()->format(__('language.timeformat'))) !!}
                @endif
            </a>

        @elseif($isOther)
            <span class="working">
                {{ __('text.timer_set_other_todo') }}
            </span>

        @endif
    </li>

{{-- ── BUTTON VARIANT ──────────────────────────────────────────────────────────
     Used inline on cards and todo rows.
     Root element is <div> — icon only, with tooltip for text.
     ──────────────────────────────────────────────────────────────────────── --}}
@else

    <div id="timer-button-container-{{ $parentTicketId }}"
         hx-get="{{ $selfRefreshUrl }}"
         hx-trigger="timerUpdate from:body"
         hx-target="this"
         hx-swap="outerHTML"
         aria-live="assertive"
         class="tw:relative timerContainer">

        @if($isIdle)
            <a href="javascript:void(0);"
               onclick="this.classList.add('starting');"
               hx-patch="{{ $startUrl }}"
               hx-target="#timerHeadMenu"
               hx-swap="outerHTML"
               hx-vals='{"ticketId": "{{ $parentTicketId }}", "action": "start"}'
               data-tippy-content="{{ __('links.start_work') }}">
                <x-globals::elements.icon name="play_circle" style="font-size:18px; padding-top:3px;" />
            </a>

        @elseif($isActive)
            <a href="javascript:void(0);"
               onclick="this.classList.add('stopped');"
               hx-patch="{{ $stopUrl }}"
               hx-trigger="click delay:500ms"
               hx-target="#timerHeadMenu"
               hx-swap="outerHTML"
               hx-vals='{"ticketId": "{{ $parentTicketId }}", "action": "stop"}'
               data-tippy-content="@if(is_array($onTheClock)){!! strip_tags(sprintf(__('links.stop_work_started_at'), dtHelper()::createFromTimestamp($onTheClock['since'], 'UTC')->setToUserTimezone()->format(__('language.timeformat')))) !!}@else{!! strip_tags(sprintf(__('links.stop_work_started_at'), dtHelper()::now()->setToUserTimezone()->format(__('language.timeformat')))) !!}@endif">
                <x-globals::elements.icon name="stop_circle" style="font-size:18px; padding-top:3px;" />

                {{-- Particle animation elements for the stop confirmation --}}
                <div class="success-circle"></div>
                <div class="particles-container">
                    <div class="particle particle-1"></div>
                    <div class="particle particle-2"></div>
                    <div class="particle particle-3"></div>
                    <div class="particle particle-4"></div>
                    <div class="particle particle-5"></div>
                    <div class="particle particle-6"></div>
                    <div class="particle particle-7"></div>
                    <div class="particle particle-8"></div>
                </div>
            </a>

        @elseif($isOther)
            <span class="working">
                <x-globals::elements.icon name="manage_accounts"
                    style="font-size:16px; padding-top:3px; color:var(--grey);"
                    data-tippy-content="{{ __('text.timer_set_other_todo') }}" />
            </span>

        @endif
    </div>

@endif
