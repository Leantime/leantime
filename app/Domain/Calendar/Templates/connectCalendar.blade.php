{!! $tpl->displayNotification() !!}

<x-globals::elements.section-title icon="calendar_add_on">{{ __('label.connect_calendar_title') }}</x-globals::elements.section-title>
<p class="subtitle">{{ __('label.connect_calendar_description') }}</p>

<br />

{{-- iCal URL Import --}}
<x-globals::elements.accordion id="connectCalendar-ical" class="noBackground">
    <x-slot name="title">
        <x-globals::elements.icon name="calendar_month" style="margin-right: 5px;" /> {{ __('label.ical_url_title') }}
    </x-slot>
    <x-slot name="content" style="padding-top: 10px;">
        <p class="text-muted small">{{ __('label.ical_url_description') }}</p>

        <form action="{{ BASE_URL }}/calendar/connectCalendar" method="post" class="formModal">
            @csrf
            <label for="ical_name">{{ __('label.calendar_name') }}:</label>
            <x-globals::forms.text-input name="name" id="ical_name" autocomplete="off" placeholder="{{ __('label.calendar_name') }}" /><br />

            <label for="ical_url">{{ __('label.ical_url') }}:</label>
            <x-globals::forms.text-input name="url" id="ical_url" autocomplete="off" class="tw:w-full" placeholder="https://example.com/calendar.ics" /><br />

            <label for="ical_color">{{ __('label.color') }}:</label>
            <x-globals::forms.text-input :bare="true" type="text" id="ical_color" name="colorClass" autocomplete="off" value="#082236" class="simpleColorPicker"/>

            <br /><br />
            <x-globals::forms.button :submit="true" contentRole="primary" name="save">{{ __('label.import_ical_button') }}</x-globals::forms.button>
        </form>
    </x-slot>
</x-globals::elements.accordion>

{{-- Plugin-injected providers (CalDAV, Google Calendar, etc.) --}}
{{-- Note: SVG icons via {!! !!} are trusted plugin content. Plugins are responsible for sanitizing their output. --}}
@foreach($providers as $provider)
    @if($provider['id'] !== 'ical')
        <x-globals::elements.accordion id="connectCalendar-{{ e($provider['id']) }}" class="noBackground">
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
                <x-globals::forms.button element="a" href="{{ $provider['actionUrl'] }}" contentRole="primary" :formModal="($provider['actionType'] ?? 'link') === 'modal'">
                    {{ $provider['actionLabel'] }}
                </x-globals::forms.button>
            </x-slot>
        </x-globals::elements.accordion>
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
