<div class="tw-p-6" style="min-width: 400px;">
    <h4 class="tw-text-xl tw-font-semibold tw-mb-6">
        {{ __('label.calendar_settings') }}
    </h4>

    {{-- Connected Calendars Section --}}
    <div class="tw-mb-6">
        <h5 class="tw-font-semibold tw-text-base tw-mb-3">{{ __('label.connected_calendars') }}</h5>

        @if(count($externalCalendars) > 0)
            <ul class="tw-space-y-2">
                @foreach($externalCalendars as $calendar)
                    <li class="tw-flex tw-items-center tw-justify-between tw-p-3 tw-bg-gray-50 tw-rounded-lg">
                        <div class="tw-flex tw-items-center">
                            <span class="tw-w-3 tw-h-3 tw-rounded-full tw-mr-3" style="background-color: {{ $calendar['colorClass'] }}"></span>
                            <span class="tw-font-medium">{{ $calendar['name'] }}</span>
                        </div>
                        <div class="tw-flex tw-items-center tw-gap-2">
                            <a href="#/calendar/editExternal/{{ $calendar['id'] }}"
                               class="btn btn-link btn-sm tw-p-1"
                               data-tippy-content="{{ __('label.edit') }}">
                                <i class="fa fa-pen"></i>
                            </a>
                            <a href="#/calendar/delExternalCalendar/{{ $calendar['id'] }}"
                               class="btn btn-link btn-sm tw-p-1 delete"
                               data-tippy-content="{{ __('label.delete') }}">
                                <i class="fa fa-trash"></i>
                            </a>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="tw-text-gray-500 tw-text-sm tw-italic">
                {{ __('label.no_connected_calendars') }}
            </p>
        @endif
    </div>

    {{-- Plugin-injected sections (Google Calendar, etc.) --}}
    @foreach($pluginSections as $section)
        <div class="tw-mb-6 tw-pt-4 tw-border-t tw-border-gray-200">
            <h5 class="tw-font-semibold tw-text-base tw-mb-3">
                @if(isset($section['icon']))
                    @if(($section['iconType'] ?? 'fontawesome') === 'svg')
                        <span class="tw-inline-block tw-w-5 tw-h-5 tw-mr-2 tw-align-middle">{!! $section['icon'] !!}</span>
                    @else
                        <i class="{{ $section['icon'] }} tw-mr-2"></i>
                    @endif
                @endif
                {{ $section['title'] }}
            </h5>

            @if(isset($section['description']))
                <p class="tw-text-gray-600 tw-text-sm tw-mb-3">{{ $section['description'] }}</p>
            @endif

            @if(isset($section['content']))
                {!! $section['content'] !!}
            @endif

            @if(isset($section['actions']))
                <div class="tw-flex tw-flex-wrap tw-gap-2">
                    @foreach($section['actions'] as $action)
                        <a href="{{ $action['url'] }}"
                           class="btn {{ $action['class'] ?? 'btn-default' }} {{ ($action['type'] ?? 'link') === 'modal' ? 'formModal' : '' }}">
                            @if(isset($action['icon']))
                                <i class="{{ $action['icon'] }} tw-mr-1"></i>
                            @endif
                            {{ $action['label'] }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    @endforeach

    @dispatchEvent('afterCalendarSettings')
</div>
