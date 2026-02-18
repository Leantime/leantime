{!! $tpl->displayNotification() !!}

<h4 class="widgettitle title-light"><i class="fa-regular fa-calendar-plus"></i> {{ __('label.connect_calendar_title') }}</h4>
<p class="subtitle">{{ __('label.connect_calendar_description') }}</p>

<br />

{{-- iCal URL Import --}}
<div class="boxedContent">
    <h5><i class="fa fa-calendar-alt"></i> {{ __('label.ical_url_title') }}</h5>
    <p class="text-muted small">{{ __('label.ical_url_description') }}</p>

    <form action="{{ BASE_URL }}/calendar/connectCalendar" method="post" class="formModal">
        @csrf
        <label for="ical_name">{{ __('label.calendar_name') }}:</label>
        <x-global::forms.input name="name" id="ical_name" autocomplete="off" placeholder="{{ __('label.calendar_name') }}" /><br />

        <label for="ical_url">{{ __('label.ical_url') }}:</label>
        <x-global::forms.input name="url" id="ical_url" autocomplete="off" style="width:100%;" placeholder="https://example.com/calendar.ics" /><br />

        <label for="ical_color">{{ __('label.color') }}:</label>
        <input type="text" id="ical_color" name="colorClass" autocomplete="off" value="#082236" class="simpleColorPicker"/>

        <br /><br />
        <x-global::button submit type="primary" name="save">{{ __('label.import_ical_button') }}</x-global::button>
    </form>
</div>

<br />

{{-- Plugin-injected providers (Google Calendar, etc.) --}}
{{-- Note: SVG icons via {!! !!} are trusted plugin content. Plugins are responsible for sanitizing their output. --}}
@foreach($providers as $provider)
    @if($provider['id'] !== 'ical')
        <div class="boxedContent">
            <h5>
                @if(($provider['iconType'] ?? 'fontawesome') === 'svg')
                    {!! $provider['icon'] !!}
                @else
                    <i class="{{ e($provider['icon']) }}"></i>
                @endif
                {{ $provider['title'] }}
            </h5>
            <p class="text-muted small">{{ $provider['description'] }}</p>
            <x-global::button link="{{ $provider['actionUrl'] }}" type="primary" :formModal="($provider['actionType'] ?? 'link') === 'modal'">
                {{ $provider['actionLabel'] }}
            </x-global::button>
        </div>
        <br />
    @endif
@endforeach

@dispatchEvent('afterProviders')

<p class="text-muted small">
    {{ __('label.more_integrations_hint') }}
</p>

<script type="text/javascript">
    jQuery(document).ready(function() {
        leantime.ticketsController.initSimpleColorPicker();
    });
</script>
