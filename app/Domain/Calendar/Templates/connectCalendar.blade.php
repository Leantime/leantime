<div class="tw-p-6">
    <h4 class="tw-text-xl tw-font-semibold tw-mb-2 tw-text-center">
        {{ __('label.connect_calendar_title') }}
    </h4>
    <p class="tw-text-gray-600 tw-text-center tw-mb-6">
        {{ __('label.connect_calendar_description') }}
    </p>

    <div class="tw-space-y-4">
        {{-- iCal URL Import - Embedded Form --}}
        <div class="tw-bg-white tw-rounded-lg tw-border tw-border-gray-200 tw-p-5">
            <div class="tw-flex tw-items-center tw-mb-3">
                <div class="tw-w-10 tw-h-10 tw-bg-gray-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mr-3">
                    <i class="fa fa-calendar-alt tw-text-gray-600 tw-text-lg"></i>
                </div>
                <h5 class="tw-font-semibold tw-text-base tw-mb-0">{{ __('label.ical_url_title') }}</h5>
            </div>
            <p class="tw-text-sm tw-text-gray-500 tw-mb-4">{{ __('label.ical_url_description') }}</p>

            <form action="{{ BASE_URL }}/calendar/importGCal" method="post" class="formModal">
                <div class="tw-space-y-3">
                    <div>
                        <label for="ical_name" class="tw-block tw-text-sm tw-font-medium tw-mb-1">{{ __('label.calendar_name') }}</label>
                        <input type="text" id="ical_name" name="name" autocomplete="off"
                               class="tw-w-full tw-border tw-border-gray-300 tw-rounded tw-px-3 tw-py-2 tw-text-sm"
                               placeholder="My Calendar" />
                    </div>
                    <div>
                        <label for="ical_url" class="tw-block tw-text-sm tw-font-medium tw-mb-1">{{ __('label.ical_url') }}</label>
                        <input type="text" id="ical_url" name="url" autocomplete="off"
                               class="tw-w-full tw-border tw-border-gray-300 tw-rounded tw-px-3 tw-py-2 tw-text-sm"
                               placeholder="https://calendar.google.com/calendar/ical/..." />
                    </div>
                    <div>
                        <label for="ical_color" class="tw-block tw-text-sm tw-font-medium tw-mb-1">{{ __('label.color') }}</label>
                        <input type="text" id="ical_color" name="colorClass" autocomplete="off" value="#082236" class="simpleColorPicker"/>
                    </div>
                </div>
                <div class="tw-text-center tw-mt-4">
                    <input type="submit" name="save" value="{{ __('label.import_ical_button') }}" class="btn btn-primary" />
                </div>
            </form>
        </div>

        {{-- Plugin-injected providers (Google Calendar, etc.) --}}
        @foreach($providers as $provider)
            @if($provider['id'] !== 'ical')
                <div class="tw-bg-white tw-rounded-lg tw-border tw-border-gray-200 tw-p-5 hover:tw-border-primary hover:tw-shadow-sm tw-transition-all">
                    <div class="tw-flex tw-items-center tw-mb-3">
                        <div class="tw-w-10 tw-h-10 tw-bg-gray-100 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mr-3">
                            @if(($provider['iconType'] ?? 'fontawesome') === 'svg')
                                {!! $provider['icon'] !!}
                            @else
                                <i class="{{ $provider['icon'] }} tw-text-gray-600 tw-text-lg"></i>
                            @endif
                        </div>
                        <h5 class="tw-font-semibold tw-text-base tw-mb-0">{{ $provider['title'] }}</h5>
                    </div>
                    <p class="tw-text-sm tw-text-gray-500 tw-mb-4">{{ $provider['description'] }}</p>
                    <div class="tw-text-center">
                        <a href="{{ $provider['actionUrl'] }}"
                           class="btn btn-primary {{ ($provider['actionType'] ?? 'link') === 'modal' ? 'formModal' : '' }}">
                            {{ $provider['actionLabel'] }}
                        </a>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    @dispatchEvent('afterProviders')

    <div class="tw-mt-6 tw-pt-4 tw-border-t tw-border-gray-200 tw-text-center">
        <p class="tw-text-sm tw-text-gray-500">
            {{ __('label.more_integrations_hint') }}
        </p>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function() {
        leantime.ticketsController.initSimpleColorPicker();
    });
</script>
