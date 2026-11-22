{{--
    "Coming up" panel (Controllers\ComingUp): the signed-in person's whole summary list in a
    dialog. Sections Today / Tomorrow / Later this week / Later; each row has a type icon, a
    link to the item, a plain "why" line and, for to-dos, a ⋮ menu with "Your reminders" that
    saves in place. Quiet colors only: no red, no "overdue".
--}}
@php
    $icons = [
        'task' => 'fa-regular fa-square',
        'milestone' => 'fa-solid fa-diamond',
        'project' => 'fa-regular fa-flag',
    ];
@endphp

<x-global::actions.modal-header :title="__('reminders.panel_title')" :description="__('reminders.panel_intro')" />

<div class="lt-coming-up">
    @if ($panel['isEmpty'])
        <p class="lt-coming-up-empty">{{ __('reminders.panel_empty') }}</p>
    @endif

    @foreach ($panel['sections'] as $section)
        <section class="lt-coming-up-section" aria-labelledby="comingUp-{{ $section['key'] }}">
            <h5 id="comingUp-{{ $section['key'] }}" class="lt-coming-up-heading">{{ $section['label'] }}</h5>
            <ul class="lt-coming-up-list">
                @foreach ($section['items'] as $row)
                    <li class="lt-coming-up-row" data-kind="{{ $row['kind'] }}">
                        <span class="lt-coming-up-icon" aria-hidden="true"><i class="{{ $icons[$row['kind']] ?? $icons['task'] }}"></i></span>
                        <a class="lt-coming-up-link" href="{{ $row['href'] }}">
                            <span class="lt-coming-up-title">{{ $row['title'] }}</span>
                            <span class="lt-coming-up-why">{{ $row['why'] }}</span>
                        </a>
                        @if ($row['kind'] === 'task')
                            <x-global::actions.dropdown class="lt-coming-up-actions" :aria-label="sprintf(__('reminders.panel_row_actions'), $row['title'])" floating keyboard>
                                @include('reminders::partials.taskReminderMenuMount', ['ticketId' => $row['id']])
                            </x-global::actions.dropdown>
                        @endif
                    </li>
                @endforeach
            </ul>
            @if ($section['note'])
                <p class="lt-coming-up-note">{{ $section['note'] }}</p>
            @endif
        </section>
    @endforeach

    @if ($panel['stillOpen'])
        <p class="lt-coming-up-note">{{ $panel['stillOpen'] }}</p>
    @endif
</div>

<div class="lt-coming-up-footer">
    <span>{{ $summaryOn ? sprintf(__('reminders.panel_footer_on'), $summaryTime) : __('reminders.panel_footer_off') }}</span>
    <a href="{{ BASE_URL }}/users/editOwn#notifications">{{ __('reminders.panel_footer_link') }}</a>
</div>
