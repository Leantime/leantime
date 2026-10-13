{{--
    feedback.alert — a message inside the page: "Notifications are muted for 3 people",
    "Your trial ends in 5 days", "Import finished with 2 warnings".

    Not a toast (those are jQuery.growl / lt:ui:notify) and not a card: an alert says something
    about the page or the user's situation.

    Props:
      tone         string  'info' (default) | 'success' | 'warning' | 'danger'.
      title        string  Optional bold first line.
      icon         string  Font Awesome classes; each tone has a default icon, pass '' for none.
      dismissible  bool    Shows a close button that hides the alert (not remembered).

    Slot: the message (markup allowed; escape user text at the call site).
    Slot `actions`: buttons or links after the message ("Upgrade", "Review").
    Anything else (id, class, data-*) passes through to the alert.
--}}
@props([
    'tone' => 'info',
    'title' => null,
    'icon' => null,
    'dismissible' => false,
])

@php
    $tone = in_array($tone, ['info', 'success', 'warning', 'danger'], true) ? $tone : 'info';
    $icon = $icon ?? match ($tone) {
        'success' => 'fa fa-circle-check',
        'warning' => 'fa fa-triangle-exclamation',
        'danger' => 'fa fa-circle-exclamation',
        default => 'fa fa-circle-info',
    };
    // Warnings and errors interrupt assistive tech; info and success are announced politely.
    $role = in_array($tone, ['warning', 'danger'], true) ? 'alert' : 'status';
@endphp

<div role="{{ $role }}" {{ $attributes->class(['lt-alert', 'lt-alert--' . $tone]) }}>
    @if ($icon !== '')
        <i class="{{ $icon }} lt-alert-icon" aria-hidden="true"></i>
    @endif
    <div class="lt-alert-body">
        @if ($title)
            <div class="lt-alert-title">{{ $title }}</div>
        @endif
        <div class="lt-alert-text">{{ $slot }}</div>
        @isset($actions)
            <div class="lt-alert-actions">{{ $actions }}</div>
        @endisset
    </div>
    @if ($dismissible)
        <button type="button" class="lt-alert-close" aria-label="{{ __('buttons.close') }}"
            onclick="this.closest('.lt-alert').hidden = true;">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>
    @endif
</div>
