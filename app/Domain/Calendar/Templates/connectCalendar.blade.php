{!! $tpl->displayNotification() !!}

<x-global::actions.modal-header>
    <x-slot:title><i class="fa-regular fa-calendar-plus"></i> {{ __('label.connect_calendar_title') }}</x-slot:title>
</x-global::actions.modal-header>
<p class="subtitle">{{ __('label.connect_calendar_description') }}</p>

<br />

{{-- iCal URL Import --}}
<x-global::elements.accordion id="connectCalendar-ical" open>
<x-slot:title><i class="fa fa-calendar-alt"></i> {{ __('label.ical_url_title') }}</x-slot:title>
        <p class="text-muted small">{{ __('label.ical_url_description') }}</p>

        <form action="{{ BASE_URL }}/calendar/connectCalendar" method="post" class="formModal">
            @csrf
            <x-global::forms.label for="ical_name">{{ __('label.calendar_name') }}:</x-global::forms.label>
            <x-global::forms.text-input id="ical_name" name="name" autocomplete="off" placeholder="{{ __('label.calendar_name') }}" /><br />

            <x-global::forms.label for="ical_url">{{ __('label.ical_url') }}:</x-global::forms.label>
            <x-global::forms.text-input width="full" id="ical_url" name="url" autocomplete="off" placeholder="https://example.com/calendar.ics" /><br />

            <x-global::forms.label for="ical_color">{{ __('label.color') }}:</x-global::forms.label>
            <input type="text" id="ical_color" name="colorClass" autocomplete="off" value="#082236" class="simpleColorPicker"/>

            <br /><br />
            <x-global::forms.button tag="input" inputType="submit" name="save" contentRole="primary" :labelText="__('label.import_ical_button')" />
        </form>
    </x-global::elements.accordion>

{{-- Plugin-injected providers (CalDAV, Google Calendar, etc.) --}}
{{-- Note: SVG icons via {!! !!} are trusted plugin content. Plugins are responsible for sanitizing their output. --}}
@foreach($providers as $provider)
    @if($provider['id'] !== 'ical')
        <x-global::elements.accordion id="connectCalendar-{{ e($provider['id']) }}" open>
<x-slot:title>@if(($provider['iconType'] ?? 'fontawesome') === 'svg')
                    <span style="display: inline-block; width: 18px; height: 18px; vertical-align: middle; margin-right: 5px;">{!! $provider['icon'] !!}</span>
                @else
                    <i class="{{ e($provider['icon']) }}"></i>
                @endif
                {{ $provider['title'] }}</x-slot:title>
                <p class="text-muted small">{{ $provider['description'] }}</p>
                <x-global::forms.button tag="a" link="{{ $provider['actionUrl'] }}"
                   contentRole="primary" class="{{ ($provider['actionType'] ?? 'link') === 'modal' ? 'formModal' : '' }}">
                    {{ $provider['actionLabel'] }}
                </x-global::forms.button>
            </x-global::elements.accordion>
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
