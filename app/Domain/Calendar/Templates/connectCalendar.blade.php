{!! $tpl->displayNotification() !!}

<h4 class="widgettitle title-light"><i class="fa-regular fa-calendar-plus"></i> {{ __('label.connect_calendar_title') }}</h4>
<p class="subtitle">{{ __('label.connect_calendar_description') }}</p>

<br />

{{-- iCal URL Import --}}
<x-global::accordion id="connectCalendar-ical" class="noBackground">
    <x-slot name="title">
        <i class="fa fa-calendar-alt" style="margin-right: 5px;"></i> {{ __('label.ical_url_title') }}
    </x-slot>
    <x-slot name="content" style="padding-top: 10px;">
        <p class="text-muted small">{{ __('label.ical_url_description') }}</p>

        <form action="{{ BASE_URL }}/calendar/connectCalendar" method="post" class="formModal">
            @csrf
            <label for="ical_name">{{ __('label.calendar_name') }}:</label>
            <x-global::forms.input name="name" id="ical_name" autocomplete="off" placeholder="{{ __('label.calendar_name') }}" /><br />

            <label for="ical_url">{{ __('label.ical_url') }}:</label>
            <x-global::forms.input name="url" id="ical_url" autocomplete="off" style="width:100%;" placeholder="https://example.com/calendar.ics" /><br />

            <label for="ical_color">{{ __('label.color') }}:</label>
            <x-global::forms.input :bare="true" type="text" id="ical_color" name="colorClass" autocomplete="off" value="#082236" class="simpleColorPicker"/>

            <br /><br />
            <x-global::button submit type="primary" name="save">{{ __('label.import_ical_button') }}</x-global::button>
        </form>
    </x-slot>
</x-global::accordion>

{{-- Plugin-injected providers (CalDAV, Google Calendar, etc.) --}}
{{-- Note: SVG icons via {!! !!} are trusted plugin content. Plugins are responsible for sanitizing their output. --}}
@foreach($providers as $provider)
    @if($provider['id'] !== 'ical')
        <x-global::accordion id="connectCalendar-{{ e($provider['id']) }}" class="noBackground">
            <x-slot name="title">
                @if(($provider['iconType'] ?? 'fontawesome') === 'svg')
                    <span style="display: inline-block; width: 18px; height: 18px; vertical-align: middle; margin-right: 5px;">{!! $provider['icon'] !!}</span>
                @else
                    <i class="{{ e($provider['icon']) }}" style="margin-right: 5px;"></i>
                @endif
                {{ $provider['title'] }}
            </x-slot>
            <x-slot name="content" style="padding-top: 10px;">
                <p class="text-muted small">{{ $provider['description'] }}</p>
                <x-global::button link="{{ $provider['actionUrl'] }}" type="primary" :formModal="($provider['actionType'] ?? 'link') === 'modal'">
                    {{ $provider['actionLabel'] }}
                </x-global::button>
            </x-slot>
        </x-global::accordion>
    @endif
@endforeach

@dispatchEvent('afterProviders')

<br />
<p class="text-muted small">
    {{ __('label.more_integrations_hint') }}
</p>

<script type="text/javascript">
    jQuery(document).ready(function() {
        leantime.ticketsController.initSimpleColorPicker();
    });
</script>
