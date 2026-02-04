@push('styles')
    <link rel="stylesheet" href="{!! BASE_URL !!}/dist/css/app-components.{!! get_release_version() !!}.min.css"/>
@endpush

<div class="max-w-3xl">
    <form class="onboardingModal" method="post" id="projectTitleOnboarding" action="{{ BASE_URL }}/help/firstLogin?step={{ $nextStep }}">
        <input type="hidden" name="currentStep" value="{{ $currentStep }}" />
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
            <div class="md:col-span-8">
                <h1 class="text-2xl font-semibold">{{ __('headlines.hi_there') }}</h1>
                <p class="text-base-content opacity-80">{!! __('text.get_organized_with_projects') !!}</p>

                <div class="mt-4">
                    <x-global::forms.text-input
                        id="projectName"
                        name="projectname"
                        labelText="{{ __('label.start_with_project_title') }}"
                        placeholder=""
                        variant="fullWidth"
                    />
                </div>
            </div>
            <div class="md:col-span-4">
                <div class="svgContainer mt-6 md:mt-10" style="max-width:300px;">
                    {!! file_get_contents(ROOT . "/dist/images/svg/undraw_game_day_ucx9.svg") !!}
                </div>
            </div>
        </div>
        <div class="mt-4 flex justify-end">
            <x-global::forms.button type="submit" contentRole="primary">
                {{ __('buttons.next') }}
            </x-global::forms.button>
        </div>
    </form>
</div>
