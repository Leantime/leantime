{!! $tpl->displayNotification() !!}

<h4 class="widgettitle title-light"><i class="fa-regular fa-calendar-plus"></i> {{ __('label.connect_calendar_title') }}</h4>
<p class="subtitle">{{ __('label.connect_calendar_description') }}</p>

<br />

{{-- iCal URL Import --}}
<div class="boxedContent">
    <h5><i class="fa fa-calendar-alt"></i> {{ __('label.ical_url_title') }}</h5>
    <p class="text-muted small">{{ __('label.ical_url_description') }}</p>

    <form action="{{ BASE_URL }}/calendar/importGCal" method="post" class="formModal">
        <label for="ical_name">{{ __('label.calendar_name') }}:</label>
        <input type="text" id="ical_name" name="name" autocomplete="off" placeholder="My Calendar" /><br />

        <label for="ical_url">{{ __('label.ical_url') }}:</label>
        <input type="text" id="ical_url" name="url" autocomplete="off" style="width:100%;" placeholder="https://calendar.google.com/calendar/ical/..." /><br />

        <label for="ical_color">{{ __('label.color') }}:</label>
        <input type="text" id="ical_color" name="colorClass" autocomplete="off" value="#082236" class="simpleColorPicker"/>

        <br /><br />
        <input type="submit" name="save" value="{{ __('label.import_ical_button') }}" class="btn btn-primary" />
    </form>
</div>

<br />

{{-- Plugin-injected providers (Google Calendar, etc.) --}}
@foreach($providers as $provider)
    @if($provider['id'] !== 'ical')
        <div class="boxedContent">
            <h5>
                @if(($provider['iconType'] ?? 'fontawesome') === 'svg')
                    {!! $provider['icon'] !!}
                @else
                    <i class="{{ $provider['icon'] }}"></i>
                @endif
                {{ $provider['title'] }}
            </h5>
            <p class="text-muted small">{{ $provider['description'] }}</p>
            <a href="{{ $provider['actionUrl'] }}"
               class="btn btn-primary {{ ($provider['actionType'] ?? 'link') === 'modal' ? 'formModal' : '' }}">
                {{ $provider['actionLabel'] }}
            </a>
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
