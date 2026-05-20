@extends($layout)

@section('content')

@php
    /**
     * All work-session timestamps in the database are stored in UTC.
     * The team works in IST (Asia/Kolkata = UTC+05:30), so we convert at
     * display time. Keeping storage as UTC means timezone-correctness is
     * preserved if the team ever expands to other regions.
     */
    $ist = static fn ($utcValue) => $utcValue
        ? \Carbon\Carbon::parse($utcValue, 'UTC')->setTimezone('Asia/Kolkata')
        : null;
@endphp

<x-global::pageheader :icon="'fa fa-clock-o'">
    <h1>My Work Sessions</h1>
</x-global::pageheader>

@displayNotification()

<div class="maincontent">
    <div class="maincontentinner">

        {{-- ── Stats row ── --}}
        <div class="row tw-mb-m">
            <div class="col-md-3 col-sm-6 col-xs-6">
                <div class="bigNumberBox">
                    <h3>{{ $todayTotal }}</h3>
                    <p>Today's Total</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-6">
                <div class="bigNumberBox">
                    <h3>{{ $weekTotal }}</h3>
                    <p>This Week</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-6">
                <div class="bigNumberBox">
                    <h3>{{ $totalCount }}</h3>
                    <p>Total Sessions</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-6">
                <div class="bigNumberBox">
                    @if ($activeSession)
                        <h3 class="tw-text-green-600" id="dashboard-elapsed" data-start-seconds="{{ $elapsedSeconds }}">
                            {{ $elapsedFormatted }}
                        </h3>
                        <p>Current Session</p>
                    @else
                        <h3>—</h3>
                        <p>No Active Session</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── Active session banner ── --}}
        @if ($activeSession)
        <div class="row tw-mb-m">
            <div class="col-md-12">
                <div class="tw-p-m tw-rounded tw-border tw-border-green-400 tw-bg-green-50 tw-flex tw-items-center tw-justify-between">
                    <div>
                        <i class="fa fa-circle tw-text-green-500 tw-mr-xs"></i>
                        <strong>Session running</strong> — started at
                        {{ $ist($activeSession->start_time)->format('H:i:s') }} IST
                    </div>
                    <div class="tw-flex tw-gap-xs">
                        <button
                            class="btn btn-danger btn-sm"
                            id="dashboardStopBtn"
                            data-session-id="{{ $activeSession->id }}"
                            title="Capture an end screenshot and close this session normally"
                        >
                            <i class="fa fa-stop-circle tw-mr-xs"></i> Stop Session
                        </button>
                        <button
                            class="btn btn-default btn-sm"
                            id="dashboardCancelBtn"
                            data-session-id="{{ $activeSession->id }}"
                            title="Close this session WITHOUT taking an end screenshot — use only if you closed your browser earlier"
                        >
                            <i class="fa fa-times tw-mr-xs"></i> Cancel (no screenshot)
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="row tw-mb-m">
            <div class="col-md-12">
                <div class="tw-p-m tw-rounded tw-border tw-border-gray-300 tw-bg-gray-50 tw-flex tw-items-center tw-justify-between">
                    <div>
                        <i class="fa fa-clock-o tw-mr-xs"></i>
                        No active session. Start tracking when you begin working.
                    </div>
                    <button class="btn btn-primary btn-sm" id="dashboardStartBtn">
                        <i class="fa fa-desktop tw-mr-xs"></i> Start Session
                    </button>
                </div>
            </div>
        </div>
        @endif

        {{-- ── Session history table ── --}}
        <div class="row">
            <div class="col-md-12">
                <div class="maincontentinner">
                    <h4 class="widgettitle title-light">Session History</h4>

                    @if (count($sessions) === 0)
                        <div class="tw-p-l tw-text-center">
                            <p>No sessions recorded yet. Start your first session using the button above.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="tablesorter table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Date</th>
                                        <th>Start Time (IST)</th>
                                        <th>End Time (IST)</th>
                                        <th>Duration</th>
                                        <th>Status</th>
                                        <th>Start Screenshot</th>
                                        <th>End Screenshot</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($sessions as $session)
                                    <tr>
                                        <td>{{ $session['id'] }}</td>
                                        <td>{{ $ist($session['start_time'])->format('Y-m-d') }}</td>
                                        <td>{{ $ist($session['start_time'])->format('H:i:s') }}</td>
                                        <td>
                                            @if ($session['end_time'])
                                                {{ $ist($session['end_time'])->format('H:i:s') }}
                                            @else
                                                <span class="tw-text-green-600">Running…</span>
                                            @endif
                                        </td>
                                        <td>{{ $session['duration_formatted'] }}</td>
                                        <td>
                                            @if ($session['status'] === 'running')
                                                <span class="tag tw-bg-green-100 tw-text-green-800">Running</span>
                                            @else
                                                <span class="tag">Completed</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($session['start_screenshot_url'])
                                                <a href="{{ $session['start_screenshot_url'] }}" target="_blank" class="btn btn-xs btn-default">
                                                    <i class="fa fa-image tw-mr-xs"></i> View
                                                </a>
                                            @else
                                                <span class="tw-text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($session['end_screenshot_url'])
                                                <a href="{{ $session['end_screenshot_url'] }}" target="_blank" class="btn btn-xs btn-default">
                                                    <i class="fa fa-image tw-mr-xs"></i> View
                                                </a>
                                            @else
                                                <span class="tw-text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>

@once
@push('scripts')
<script>
(function () {
    'use strict';

    // Live elapsed ticker for the dashboard stats box
    var el = document.getElementById('dashboard-elapsed');
    if (el) {
        var secs = parseInt(el.dataset.startSeconds, 10) || 0;
        setInterval(function () { secs++; el.textContent = fmt(secs); }, 1000);
    }

    function fmt(s) {
        var h = Math.floor(s / 3600), m = Math.floor((s % 3600) / 60), sec = s % 60;
        return pad(h) + ':' + pad(m) + ':' + pad(sec);
    }
    function pad(n) { return n < 10 ? '0' + n : '' + n; }

    // Standard messages — keep in sync with partials/timer.blade.php
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
                  window.isSecureContext !== false);
    }

    function captureScreenshot(cb) {
        if (!isScreenCaptureSupported()) { cb('', 'unsupported'); return; }
        navigator.mediaDevices.getDisplayMedia({ video: { frameRate: 1 }, audio: false })
            .then(function (stream) {
                var v = document.createElement('video');
                v.srcObject = stream;
                v.onloadedmetadata = function () {
                    v.play();
                    var c = document.createElement('canvas');
                    c.width = Math.min(v.videoWidth, 1920); c.height = Math.min(v.videoHeight, 1080);
                    c.getContext('2d').drawImage(v, 0, 0, c.width, c.height);
                    stream.getTracks().forEach(function (t) { t.stop(); });
                    cb(c.toDataURL('image/jpeg', 0.7).replace(/^data:image\/[a-z]+;base64,/i, ''));
                };
            })
            .catch(function () { cb('', 'denied'); });
    }

    // Up-front browser support notice — visible BEFORE any click, so unsupported users know.
    var startBtn = document.getElementById('dashboardStartBtn');
    if (startBtn && !isScreenCaptureSupported()) {
        startBtn.disabled = true;
        startBtn.setAttribute('title', 'This browser does not support screen capture. Use Chrome, Edge, or Firefox on desktop.');
        startBtn.innerHTML = '<i class="fa fa-exclamation-triangle tw-mr-xs"></i> Browser not supported';
        var banner = startBtn.closest('.row');
        if (banner) {
            var hint = document.createElement('div');
            hint.className = 'col-md-12 tw-mt-xs';
            hint.innerHTML =
                '<div class="tw-p-s tw-rounded tw-border tw-border-yellow-400 tw-bg-yellow-50 tw-text-sm">' +
                '<i class="fa fa-info-circle tw-mr-xs tw-text-yellow-700"></i>' +
                '<strong>Heads-up:</strong> your current browser does not support the screen-recording feature needed for work sessions. ' +
                'Please open this page in <strong>Chrome, Edge, or Firefox</strong> on a desktop or laptop to start a session. ' +
                '<em>(Safari and most mobile browsers are not supported yet.)</em>' +
                '</div>';
            banner.appendChild(hint);
        }
    }

    // Start button on dashboard
    if (startBtn && isScreenCaptureSupported()) {
        startBtn.addEventListener('click', function () {
            startBtn.disabled = true;
            startBtn.innerHTML = '<i class="fa fa-spinner fa-spin tw-mr-xs"></i> Capturing…';
            captureScreenshot(function (b64, reason) {
                if (!b64) {
                    alert(reason === 'unsupported' ? UNSUPPORTED_MSG : DENIED_MSG);
                    startBtn.disabled = false;
                    startBtn.innerHTML = '<i class="fa fa-desktop tw-mr-xs"></i> Start Session';
                    return;
                }
                fetch('{{ BASE_URL }}/worktracker/api', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({ screenshot: b64 })
                }).then(function (r) { return r.json(); }).then(function (data) {
                    if (data.success) { location.reload(); } else { alert(data.message); startBtn.disabled = false; startBtn.innerHTML = '<i class="fa fa-desktop tw-mr-xs"></i> Start Session'; }
                });
            });
        });
    }

    // Stop button on dashboard — gracefully degrades on unsupported browsers
    // (the end screenshot is optional; we still close the session).
    var stopBtn = document.getElementById('dashboardStopBtn');
    if (stopBtn) {
        stopBtn.addEventListener('click', function () {
            stopBtn.disabled = true;
            stopBtn.innerHTML = '<i class="fa fa-spinner fa-spin tw-mr-xs"></i> Stopping…';
            var sessionId = parseInt(stopBtn.dataset.sessionId, 10);
            var sendStop = function (b64) {
                fetch('{{ BASE_URL }}/worktracker/api', {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({ session_id: sessionId, screenshot: b64 || '' })
                }).then(function (r) { return r.json(); }).then(function () { location.reload(); });
            };
            if (!isScreenCaptureSupported()) {
                if (confirm('Your browser does not support end-of-session screen capture. Stop the session without an end screenshot?')) {
                    sendStop('');
                } else {
                    stopBtn.disabled = false;
                    stopBtn.innerHTML = '<i class="fa fa-stop-circle tw-mr-xs"></i> Stop Session';
                }
                return;
            }
            captureScreenshot(function (b64) { sendStop(b64); });
        });
    }

    // Cancel-orphan button (closes the session WITHOUT an end screenshot)
    var cancelBtn = document.getElementById('dashboardCancelBtn');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function () {
            if (!confirm('Cancel this session without an end screenshot? Duration will be calculated from start until now. Only use this if you closed your browser earlier.')) {
                return;
            }
            cancelBtn.disabled = true;
            cancelBtn.innerHTML = '<i class="fa fa-spinner fa-spin tw-mr-xs"></i> Cancelling…';
            var sessionId = parseInt(cancelBtn.dataset.sessionId, 10);
            fetch('{{ BASE_URL }}/worktracker/api', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ session_id: sessionId })
            }).then(function (r) { return r.json(); }).then(function (data) {
                if (data.success) { location.reload(); }
                else { alert(data.message || 'Could not cancel session.'); cancelBtn.disabled = false; cancelBtn.innerHTML = '<i class="fa fa-times tw-mr-xs"></i> Cancel (no screenshot)'; }
            });
        });
    }
}());
</script>
@endpush
@endonce

@endsection
