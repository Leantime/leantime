{!! $tpl->displayNotification() !!}

<x-globals::elements.section-title icon="settings">{{ __('label.calendar_settings') }}</x-globals::elements.section-title>

<br />

{{-- Connected Calendars Section --}}
<h5 class="subtitle">{{ __('label.connected_calendars') }}</h5>

@if(count($externalCalendars) > 0)
    <ul class="simpleList tw:mb-5">
        @foreach($externalCalendars as $calendar)
            <li class="tw:flex tw:justify-between tw:items-center tw:p-2 tw:mb-2" style="background: var(--secondary-background); border-radius: var(--box-radius-small);">
                <span>
                    <span class="indicatorCircle" style="background-color: {{ e($calendar['colorClass']) }};"></span>
                    <strong>{{ $calendar['name'] }}</strong>
                    @if(!empty($calendar['subtitle']))
                        <br><small class="text-muted">{!! $calendar['subtitle'] !!}</small>
                    @endif
                </span>
                <span class="tw:whitespace-nowrap">
                    @if(!empty($calendar['actions']))
                        {{-- Plugin-provided per-calendar actions --}}
                        @foreach($calendar['actions'] as $action)
                            <a href="{{ $action['url'] }}"
                               class="{{ ($action['type'] ?? 'link') === 'modal' ? 'formModal' : '' }}"
                               data-tippy-content="{{ $action['tooltip'] ?? '' }}">
                                <i class="{{ e($action['icon']) }}"></i>
                            </a>
                            &nbsp;
                        @endforeach
                    @elseif(empty($calendar['managedByPlugin']))
                        {{-- Default iCal calendar actions --}}
                        <a href="#/calendar/editExternal/{{ $calendar['id'] }}" class="formModal" data-tippy-content="{{ __('label.edit') }}">
                            <x-globals::elements.icon name="edit" />
                        </a>
                        &nbsp;
                        <a href="#/calendar/delExternalCalendar/{{ $calendar['id'] }}" class="delete" data-tippy-content="{{ __('label.delete') }}">
                            <x-globals::elements.icon name="delete" />
                        </a>
                    @endif
                </span>
            </li>
        @endforeach
    </ul>
@else
    <p class="text-muted small tw:italic">
        {{ __('label.no_connected_calendars') }}
    </p>
    <br />
@endif

{{-- Plugin-injected sections (Google Calendar, etc.) --}}
{{-- Note: Plugin content via {!! !!} is trusted. Plugins are responsible for sanitizing their output. --}}
@foreach($pluginSections as $section)
    <hr class="tw:my-5" />

    <h5 class="subtitle">
        @if(isset($section['icon']))
            @if(($section['iconType'] ?? 'fontawesome') === 'svg')
                <span class="tw:inline-block tw:w-5 tw:h-5 tw:align-middle tw:mr-2">{!! $section['icon'] !!}</span>
            @else
                <i class="{{ e($section['icon']) }} tw:mr-2"></i>
            @endif
        @endif
        {{ $section['title'] }}
    </h5>

    @if(isset($section['description']))
        <p class="text-muted small">{{ $section['description'] }}</p>
    @endif

    @if(isset($section['content']))
        {!! $section['content'] !!}
    @endif

    @if(isset($section['actions']))
        <div class="tw:mt-2">
            @foreach($section['actions'] as $action)
                <x-globals::forms.button
                    element="a"
                    href="{{ $action['url'] }}"
                    :class="(($action['type'] ?? 'link') === 'modal' ? 'formModal ' : '') . ($action['class'] ?? '')"
                    contentRole="{{ str_replace(['btn-default', 'btn-primary', 'btn-secondary'], ['secondary', 'primary', 'secondary'], $action['class'] ?? 'btn-default') }}"
                >
                    @if(isset($action['icon']))
                        <i class="{{ e($action['icon']) }}"></i>
                    @endif
                    {{ $action['label'] }}
                </x-globals::forms.button>
            @endforeach
        </div>
    @endif
@endforeach

@dispatchEvent('afterCalendarSettings')
