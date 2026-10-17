@extends($layout)

@section('content')

<div class="errortitle">

    <h4 class="animate0 fadeInUp">{{ sprintf(__('headlines.module_switched_off'), $moduleTitle) }}</h4>
    <p class="animate1 fadeInUp">{{ __('text.module_switched_off') }}</p>
    <div class="errorbtns animate2 fadeInUp">
        <x-global::forms.button tag="a" contentRole="default" onclick="history.back()">{!! __('buttons.back') !!}</x-global::forms.button>
        @if ($projectSettingsUrl)
            <x-global::forms.button tag="a" :link="$projectSettingsUrl" contentRole="primary">{{ __('links.manage_project_modules') }}</x-global::forms.button>
        @endif
    </div><br/><br/><br/><br/>

</div>

@endsection
