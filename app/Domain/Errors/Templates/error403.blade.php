@extends($layout)

@section('content')

<div class="errortitle">

    <h4 class="animate0 fadeInUp">{!! __('headlines.page_forbidden') !!}</h4>
    <span class="animate1 bounceIn">4</span>
    <span class="animate2 bounceIn">0</span>
    <span class="animate3 bounceIn">3</span>
    <div class="errorbtns animate4 fadeInUp">
        <x-global::forms.button tag="a" contentRole="default" onclick="history.back()">{!! __('buttons.back') !!}</x-global::forms.button>
        <x-global::forms.button tag="a" link="{{ BASE_URL }}" contentRole="primary">{!! __('links.dashboard') !!}</x-global::forms.button>
    </div><br/><br/><br/><br/>

</div>

@endsection
