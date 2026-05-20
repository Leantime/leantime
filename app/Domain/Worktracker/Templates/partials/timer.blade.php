{{--
  Navbar timer widget — rendered by WorkTracker\Hxcontrollers\Timer::getStatus().
  Polled every 30 s when a session is running; otherwise only re-renders on workTrackerUpdate event.
--}}
@if ($login::userIsAtLeast(\Leantime\Domain\Auth\Models\Roles::$editor, true))

<li
    class="timerHeadMenu workTrackerTimerWidget"
    id="workTrackerTimerWidget"
    hx-get="{{ BASE_URL }}/hx/worktracker/timer/get-status"
    hx-trigger="workTrackerUpdate from:body{{ isset($timerStatus['running']) && $timerStatus['running'] ? ', every 30s' : '' }}"
    hx-swap="outerHTML"
>
    @if (isset($timerStatus['running']) && $timerStatus['running'])
        {{-- ── Running state ── --}}
        <a
            href="javascript:void(0);"
            class="dropdown-toggle workTracker-running"
            data-toggle="dropdown"
            data-tippy-content="Work Session Running (screen-recorded shift)"
        >
            <i class="fa fa-desktop tw-text-green-500 tw-mr-xs"></i>
            <span class="workTracker-elapsed" id="workTracker-elapsed" data-start-seconds="{{ $timerStatus['elapsed_seconds'] }}">
                {{ $formattedTime }}
            </span>
        </a>

        <ul class="dropdown-menu pull-right">
            <li class="nav-header">Work Session Active</li>
            <li>
                <a href="{{ BASE_URL }}/worktracker/showDashboard">
                    <i class="fa fa-clock-o tw-mr-xs"></i> My Sessions
                </a>
            </li>
            @if ($login::userIsAtLeast(\Leantime\Domain\Auth\Models\Roles::$admin, true))
            <li>
                <a href="{{ BASE_URL }}/worktracker/showAdmin">
                    <i class="fa fa-users tw-mr-xs"></i> Admin Monitor
                </a>
            </li>
            @endif
            <li class="divider"></li>
            <li>
                <a
                    href="javascript:void(0);"
                    id="workTracker-stopBtn"
                    data-session-id="{{ (int) ($timerStatus['session_id'] ?? 0) }}"
                    title="One-click stop. To save an end screenshot, use the Stop button on the My Sessions page."
                >
                    <i class="fa fa-stop-circle tw-mr-xs tw-text-red-500"></i> Stop Session
                </a>
            </li>
            <li>
                <a href="{{ BASE_URL }}/worktracker/showDashboard" title="Open My Sessions to stop with an end screenshot">
                    <i class="fa fa-camera tw-mr-xs tw-text-blue-500"></i> Stop with screenshot…
                </a>
            </li>
        </ul>
    @else
        {{-- ── Idle state ── --}}
        <a
            href="javascript:void(0);"
            class="dropdown-toggle workTracker-idle"
            data-toggle="dropdown"
            data-tippy-content="Work Session Tracker"
        >
            <i class="fa fa-desktop tw-mr-xs"></i> Start Session
        </a>

        <ul class="dropdown-menu pull-right">
            <li class="nav-header">Work Session Tracker</li>
            <li>
                <a href="javascript:void(0);" id="workTracker-startBtn">
                    <i class="fa fa-play-circle tw-mr-xs tw-text-green-600"></i> Start a session (screen capture)
                </a>
            </li>
            <li class="divider"></li>
            <li>
                <a href="{{ BASE_URL }}/worktracker/showDashboard">
                    <i class="fa fa-clock-o tw-mr-xs"></i> My Sessions
                </a>
            </li>
            @if ($login::userIsAtLeast(\Leantime\Domain\Auth\Models\Roles::$admin, true))
            <li>
                <a href="{{ BASE_URL }}/worktracker/showAdmin">
                    <i class="fa fa-users tw-mr-xs"></i> Admin Monitor
                </a>
            </li>
            @endif
        </ul>
    @endif
</li>

@once
@push('scripts')
<script>
(function () {
    'use strict';

    // ── Live tick: increment the displayed time every second when running ──
    // Must re-attach to the elapsed span every time HTMX swaps in a new widget,
    // otherwise the timer stays frozen at the value rendered server-side.
    var currentTicker = null;

    function attachTicker() {
        if (currentTicker) { clearInterval(currentTicker); currentTicker = null; }
        var el = document.getElementById('workTracker-elapsed');
        if (!el) return;
        var seconds = parseInt(el.dataset.startSeconds, 10) || 0;
        el.textContent = formatDuration(seconds);
        currentTicker = setInterval(function () {
            seconds++;
            el.textContent = formatDuration(seconds);
        }, 1000);
    }

    function formatDuration(s) {
        var h = Math.floor(s / 3600);
        var m = Math.floor((s % 3600) / 60);
        var sec = s % 60;
        return pad(h) + ':' + pad(m) + ':' + pad(sec);
    }

    function pad(n) { return n < 10 ? '0' + n : '' + n; }

    // First page load
    attachTicker();

    // Re-attach after every HTMX swap that touches the widget
    document.body.addEventListener('htmx:afterSwap', function (evt) {
        var target = evt && evt.detail && (evt.detail.target || evt.detail.elt);
        if (target && (target.id === 'workTrackerTimerWidget' || (target.closest && target.closest('#workTrackerTimerWidget')))) {
            attachTicker();
        } else if (document.getElementById('workTracker-elapsed')) {
            attachTicker();
        }
    });

    var BASE_URL = '{{ BASE_URL }}';

    // Standard messages — keep in sync with showDashboard.blade.php
    var UNSUPPORTED_MSG =
        'Your browser does not support the screen-recording feature needed to start a work session.\n\n' +
        'Please use the latest version of Google Chrome, Microsoft Edge, or Mozilla Firefox on a desktop or laptop computer.\n\n' +
        'Safari and most mobile browsers do not yet support this feature.';
    var DENIED_MSG =
        'Screen sharing was not allowed.\n\n' +
        'When you click Start Session, your browser will ask permission to share your screen. ' +
        'Please choose a screen or window and click "Share" to begin tracking.';

    function isScreenCaptureSupported() {
        return !!(window.navigator && navigator.mediaDevices &&
                  typeof navigator.mediaDevices.getDisplayMedia === 'function' &&
                  window.isSecureContext !== false); // getDisplayMedia requires HTTPS or localhost
    }

    // ── Start button ──
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('#workTracker-startBtn');
        if (!btn) return;

        // Hard gate: stop early on Safari / unsupported browsers with a clear message.
        if (!isScreenCaptureSupported()) {
            showNotification(UNSUPPORTED_MSG, 'error');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin tw-mr-xs"></i> Capturing screen…';

        captureScreenshot(function (base64, reason) {
            if (!base64) {
                showNotification(reason === 'unsupported' ? UNSUPPORTED_MSG : DENIED_MSG, 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-desktop tw-mr-xs"></i> Start Session';
                return;
            }

            btn.innerHTML = '<i class="fa fa-spinner fa-spin tw-mr-xs"></i> Starting session…';

            fetch(BASE_URL + '/worktracker/api', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ screenshot: base64 })
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    // Refresh the navbar widget via HTMX
                    htmx.trigger(document.body, 'workTrackerUpdate');
                } else {
                    showNotification(data.message || 'Could not start session.', 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa fa-desktop tw-mr-xs"></i> Start Session';
                }
            })
            .catch(function () {
                showNotification('Network error. Please try again.', 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-desktop tw-mr-xs"></i> Start Session';
            });
        });
    });

    // ── Stop button (quick stop — no end-screenshot prompt) ──
    // The navbar Stop is one-click for ergonomics. The end-screenshot flow
    // is still available from My Sessions → Stop Session button if needed.
    //
    // IMPORTANT: read sessionId from the data-attribute at click time, NOT
    // from a Blade-baked literal. The script block lives inside the once+
    // push wrapper below and is therefore rendered ONLY at the initial
    // page load. After HTMX swaps in the running-state widget, any baked-in
    // JS literal would still be 0 (the value at page load) — which made
    // every Stop click show "No active session to stop."
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('#workTracker-stopBtn');
        if (!btn) return;

        var sessionId = parseInt(btn.dataset.sessionId || '0', 10);
        if (sessionId <= 0) {
            // Fallback: ask the server for the current status before complaining
            fetch(BASE_URL + '/worktracker/api', {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data && data.running && data.session_id) {
                    btn.dataset.sessionId = data.session_id;
                    btn.click();
                } else {
                    showNotification('No active session to stop.', 'error');
                }
            })
            .catch(function () {
                showNotification('No active session to stop.', 'error');
            });
            return;
        }

        btn.innerHTML = '<i class="fa fa-spinner fa-spin tw-mr-xs"></i> Stopping…';

        fetch(BASE_URL + '/worktracker/api', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ session_id: sessionId })
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                htmx.trigger(document.body, 'workTrackerUpdate');
            } else {
                showNotification(data.message || 'Could not stop session.', 'error');
            }
        })
        .catch(function () {
            showNotification('Network error. Please try again.', 'error');
        });
    });

    /**
     * Capture a single frame using the browser Screen Capture API.
     * Calls back as (base64, reason) where reason is:
     *   - undefined on success
     *   - 'unsupported' when the browser does not implement getDisplayMedia
     *   - 'denied'      when the user dismissed the picker / blocked permission
     */
    function captureScreenshot(callback) {
        if (!isScreenCaptureSupported()) {
            callback('', 'unsupported');
            return;
        }

        navigator.mediaDevices.getDisplayMedia({ video: { frameRate: 1 }, audio: false })
            .then(function (stream) {
                var video   = document.createElement('video');
                video.srcObject = stream;
                video.onloadedmetadata = function () {
                    video.play();
                    var canvas  = document.createElement('canvas');
                    canvas.width  = Math.min(video.videoWidth, 1920);
                    canvas.height = Math.min(video.videoHeight, 1080);
                    canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
                    stream.getTracks().forEach(function (t) { t.stop(); });
                    var dataUrl = canvas.toDataURL('image/jpeg', 0.7);
                    // Strip the data-URI prefix before sending
                    callback(dataUrl.replace(/^data:image\/[a-z]+;base64,/i, ''));
                };
            })
            .catch(function () { callback('', 'denied'); });
    }

    function showNotification(msg, type) {
        if (typeof leantime !== 'undefined' && leantime.notification) {
            leantime.notification.show(msg, type);
        } else {
            alert(msg);
        }
    }
}());
</script>
@endpush
@endonce

@endif
