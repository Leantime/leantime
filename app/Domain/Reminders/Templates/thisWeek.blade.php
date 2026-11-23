{{--
    "Your week" panel (Controllers\ThisWeek): the signed-in person's weekly summary in a dialog.
    Sections This week / Last week / plugin sections, built like the "Coming up" panel and using
    its styles. Quiet colors only: no red, no "overdue", no comparisons.
--}}
@php
    $icons = [
        'task' => 'fa-regular fa-square',
        'milestone' => 'fa-solid fa-diamond',
        'project' => 'fa-regular fa-flag',
        'done' => 'fa-regular fa-square-check',
    ];
@endphp

<x-global::actions.modal-header :title="__('reminders.weekly_title')" :description="__('reminders.weekly_panel_intro')" />

<div class="lt-coming-up">
    @if ($panel['isEmpty'])
        <p class="lt-coming-up-empty">{{ __('reminders.weekly_panel_empty') }}</p>
    @endif

    @foreach ($panel['sections'] as $section)
        <section class="lt-coming-up-section" aria-labelledby="thisWeek-{{ $section['key'] }}">
            <h5 id="thisWeek-{{ $section['key'] }}" class="lt-coming-up-heading">{{ $section['label'] }}</h5>
            @if ($section['items'] !== [])
                <ul class="lt-coming-up-list">
                    @foreach ($section['items'] as $row)
                        <li class="lt-coming-up-row" data-kind="{{ $row['kind'] }}">
                            <span class="lt-coming-up-icon" aria-hidden="true"><i class="{{ $icons[$row['kind']] ?? $icons['task'] }}"></i></span>
                            <a class="lt-coming-up-link" href="{{ $row['href'] }}">
                                <span class="lt-coming-up-title">{{ $row['title'] }}</span>
                                @if ($row['why'] !== '')
                                    <span class="lt-coming-up-why">{{ $row['why'] }}</span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
            @foreach ($section['notes'] as $note)
                <p class="lt-coming-up-note">{{ $note }}</p>
            @endforeach
            @if ($section['link'])
                <p class="lt-coming-up-note"><a href="{{ $section['link']['url'] }}">{{ $section['link']['label'] }}</a></p>
            @endif
        </section>
    @endforeach
</div>

<div class="lt-coming-up-footer">
    <span>{{ $weeklyOn ? sprintf(__('reminders.weekly_panel_footer_on'), $weeklyDay, $weeklyTime) : __('reminders.weekly_panel_footer_off') }}</span>
    <a href="{{ BASE_URL }}/users/editOwn#notifications">{{ __('reminders.panel_footer_link') }}</a>
</div>
