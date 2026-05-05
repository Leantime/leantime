@props([
    'session' => [],
    'view' => 'employee', // employee or manager
])

@php
    $statusClass = match($session['status'] ?? '') {
        'completed' => 'success',
        'cancelled' => 'default',
        'in_progress' => 'warning',
        default => 'info',
    };
    $otherName = $view === 'employee'
        ? trim(($session['managerFirstname'] ?? '') . ' ' . ($session['managerLastname'] ?? ''))
        : trim(($session['employeeFirstname'] ?? '') . ' ' . ($session['employeeLastname'] ?? ''));
    $otherLabel = $view === 'employee' ? __('label.oneonone.with_manager') : __('label.oneonone.with_employee');
@endphp

<li class="tw-mb-s tw-rounded"
    style="border:1px solid var(--main-border-color); background:var(--secondary-background);">
    <a href="{{ BASE_URL }}/oneonone/showSession/{{ $session['id'] }}"
       class="tw-block tw-p-m tw-no-underline"
       style="color:inherit;">

        <div class="tw-flex tw-justify-between tw-items-start tw-gap-s tw-flex-wrap">
            <div class="tw-flex-1 tw-min-w-0">
                <div class="tw-flex tw-items-center tw-gap-s tw-mb-xs tw-flex-wrap">
                    <strong>
                        @if (!empty($session['meetingDate']))
                            {{ dtHelper()->parseDbDateTime($session['meetingDate'])->setToUserTimezone()->format(__('language.dateformat')) }}
                        @else
                            —
                        @endif
                    </strong>
                    <span class="label label-{{ $statusClass }}">
                        {{ __('oneonone.status.' . ($session['status'] ?? 'scheduled')) }}
                    </span>
                </div>
                @if (!empty($session['title']))
                    <div class="tw-mb-xs">{{ $session['title'] }}</div>
                @endif
                <small style="color:var(--grey);">
                    {{ $otherLabel }}: <strong>{{ $otherName ?: '—' }}</strong>
                </small>
                @if (!empty($session['summary']))
                    <p class="tw-mt-s tw-text-sm tw-mb-0" style="color:var(--grey);">
                        {{ \Illuminate\Support\Str::limit(strip_tags($session['summary']), 160) }}
                    </p>
                @endif
            </div>
            <span class="fa fa-chevron-right" style="color:var(--grey);"></span>
        </div>
    </a>
</li>
