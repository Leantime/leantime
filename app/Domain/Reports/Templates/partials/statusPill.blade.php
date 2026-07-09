{{--
    Colored project status pill (green/yellow/red from the latest status update).

    Expects:
    $status: string|null - green|yellow|red (null = no update yet)
    $date:   \Carbon\CarbonImmutable|null - when the status was posted (optional)
--}}
@php
    $pillColors = [
        'green' => 'var(--green)',
        'yellow' => 'var(--yellow)',
        'red' => 'var(--red)',
    ];
    $pillLabels = [
        'green' => __('label.status_on_track'),
        'yellow' => __('label.status_at_risk'),
        'red' => __('label.status_off_track'),
    ];
@endphp

@if (!empty($status) && isset($pillColors[$status]))
    <span class="reportStatusPill tw-inline-flex tw-items-center tw-gap-1.5 tw-text-sm tw-whitespace-nowrap">
        <span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:{{ $pillColors[$status] }};"></span>
        {{ $pillLabels[$status] }}
        @if (!empty($date))
            <span class="tw-opacity-60">· {{ $date->formatDateForUser() }}</span>
        @endif
    </span>
@else
    <span class="reportStatusPill tw-inline-flex tw-items-center tw-gap-1.5 tw-text-sm tw-opacity-60 tw-whitespace-nowrap">
        <span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:var(--grey);"></span>
        {{ __('label.status_no_update') }}
    </span>
@endif
