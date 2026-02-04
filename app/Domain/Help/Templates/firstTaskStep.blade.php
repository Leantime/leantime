@push('styles')
    <link rel="stylesheet" href="{!! BASE_URL !!}/dist/css/app-components.{!! get_release_version() !!}.min.css"/>
@endpush

<div class="max-w-3xl">
    <form class="onboardingModal" method="post" id="firstTaskOnboarding" action="{{ BASE_URL }}/help/firstLogin?step={{ $nextStep }}">
        <input type="hidden" name="currentStep" value="{{ $currentStep }}" />
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
            <div class="md:col-span-8">
                <h1 class="text-2xl font-semibold">{{ __('headlines.welcome_to_leantime') }}</h1>
                <p class="text-base-content opacity-80">{{ __('text.lets_start_with_first_task') }}</p>

                <div class="mt-4">
                    <x-global::forms.text-input
                        id="firstTask"
                        name="headline"
                        labelText="{{ __('label.whats_one_thing_to_do_today') }}"
                        placeholder="{{ __('input.placeholder.finish_slide_deck') }}"
                        required
                        variant="fullWidth"
                    />
                    <p class="text-sm text-base-content opacity-70 mt-2">{{ __('text.first_task_help') }}</p>
                </div>

                <div class="mt-4">
                    <x-global::forms.button type="submit" contentRole="primary">
                        {{ __('buttons.lets_go') }}
                    </x-global::forms.button>
                </div>
            </div>
            <div class="md:col-span-4">
                <div class="svgContainer mt-6 md:mt-10" style="max-width:300px;">
                    {!! file_get_contents(ROOT . "/dist/images/svg/undraw_happy_news_re_tsbd.svg") !!}
                </div>
            </div>
        </div>
    </form>
</div>
