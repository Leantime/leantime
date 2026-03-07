{!! $tpl->displayNotification() !!}

<h4 class="widgettitle title-light"><x-global::elements.icon name="settings" /> {{ __('label.calendar_settings') }}</h4>

<br />

{{-- Connected Calendars Section --}}
<h5 class="subtitle">{{ __('label.connected_calendars') }}</h5>

@if(count($externalCalendars) > 0)
    <ul class="simpleList" style="margin-bottom: 20px;">
        @foreach($externalCalendars as $calendar)
            <li style="padding: 10px; background: var(--secondary-background); border-radius: var(--box-radius-small); margin-bottom: 8px; display: flex; justify-content: space-between; align-items: center;">
                <span>
                    <span class="indicatorCircle" style="background-color: {{ e($calendar['colorClass']) }};"></span>
                    <strong>{{ $calendar['name'] }}</strong>
                    @if(!empty($calendar['subtitle']))
                        <br><small class="text-muted">{!! $calendar['subtitle'] !!}</small>
                    @endif
                </span>
                <span style="white-space: nowrap;">
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
                            <x-global::elements.icon name="edit" />
                        </a>
                        &nbsp;
                        <a href="#/calendar/delExternalCalendar/{{ $calendar['id'] }}" class="delete" data-tippy-content="{{ __('label.delete') }}">
                            <x-global::elements.icon name="delete" />
                        </a>
                    @endif
                </span>
            </li>
        @endforeach
    </ul>
@else
    <p class="text-muted small" style="font-style: italic;">
        {{ __('label.no_connected_calendars') }}
    </p>
    <br />
@endif

{{-- Plugin-injected sections (Google Calendar, etc.) --}}
{{-- Note: Plugin content via {!! !!} is trusted. Plugins are responsible for sanitizing their output. --}}
@foreach($pluginSections as $section)
    <hr style="margin: 20px 0;" />

    <h5 class="subtitle">
        @if(isset($section['icon']))
            @if(($section['iconType'] ?? 'fontawesome') === 'svg')
                <span style="display: inline-block; width: 20px; height: 20px; vertical-align: middle; margin-right: 8px;">{!! $section['icon'] !!}</span>
            @else
                <i class="{{ e($section['icon']) }}" style="margin-right: 8px;"></i>
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
        <div style="margin-top: 10px;">
            @foreach($section['actions'] as $action)
                <a href="{{ $action['url'] }}"
                   class="btn {{ $action['class'] ?? 'btn-default' }} {{ ($action['type'] ?? 'link') === 'modal' ? 'formModal' : '' }}">
                    @if(isset($action['icon']))
                        <i class="{{ e($action['icon']) }}"></i>
                    @endif
                    {{ $action['label'] }}
                </a>
            @endforeach
        </div>
    @endif
@endforeach

@dispatchEvent('afterCalendarSettings')
