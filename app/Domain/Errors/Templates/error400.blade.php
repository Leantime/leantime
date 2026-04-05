@php
    $headline = isset($tpl) ? $tpl->__('headlines.bad_request') : 'Bad Request';
    $headline = ($headline === 'headlines.bad_request') ? 'Bad Request' : $headline;
    $backLabel = isset($tpl) ? $tpl->__('buttons.back') : 'Back';
    $backLabel = ($backLabel === 'buttons.back') ? 'Back' : $backLabel;
    $dashLabel = isset($tpl) ? $tpl->__('links.dashboard') : 'Dashboard';
    $dashLabel = ($dashLabel === 'links.dashboard') ? 'Dashboard' : $dashLabel;
@endphp
<div class="errortitle">

    <h4 class="animate0 fadeInUp">{{ $headline }}</h4>
    <span class="animate1 bounceIn">4</span>
    <span class="animate2 bounceIn">0</span>
    <span class="animate3 bounceIn">0</span>
    <div class="errorbtns animate4 fadeInUp">
        <x-globals::forms.button element="a" href="#" contentRole="secondary" onclick="history.back()">{{ $backLabel }}</x-globals::forms.button>
        <x-globals::forms.button element="a" href="{{ BASE_URL }}" contentRole="primary">{{ $dashLabel }}</x-globals::forms.button>
    </div><br/><br/><br/><br/>

</div>
