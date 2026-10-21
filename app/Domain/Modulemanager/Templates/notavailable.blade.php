@extends($layout)

@section('content')

<div class="maincontent">
    <x-global::layout.content-box class="tw-text-center tw-py-12">

        <x-global::undrawSvg image="undraw_a_moment_to_relax_bbpa.svg" style="color:var(--main-titles-color);" maxWidth="260px" />

        <h3 class="tw-mt-6">{{ sprintf(__('headlines.module_switched_off'), $moduleTitle) }}</h3>
        <p class="tw-mt-2">{{ __('text.module_switched_off') }}</p>

        <div class="tw-mt-6 tw-flex tw-justify-center tw-gap-2">
            <x-global::forms.button tag="button" contentRole="default" onclick="history.back()">{!! __('buttons.back') !!}</x-global::forms.button>
            @if ($projectSettingsUrl)
                <x-global::forms.button tag="a" :link="$projectSettingsUrl" contentRole="primary">{{ __('links.manage_project_modules') }}</x-global::forms.button>
            @endif
        </div>

    </x-global::layout.content-box>
</div>

@endsection
