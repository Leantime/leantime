<div class="errortitle">

    <h4 class="animate0 fadeInUp">{{ $tpl->__('headlines.page_not_found') }}</h4>
    <span class="animate1 bounceIn">4</span>
    <span class="animate2 bounceIn">0</span>
    <span class="animate3 bounceIn">4</span>
    <div class="errorbtns animate4 fadeInUp">
        <x-globals::forms.button link="#" type="secondary" onclick="history.back()">{{ $tpl->__('buttons.back') }}</x-globals::forms.button>
        <x-globals::forms.button link="{{ BASE_URL }}" type="primary">{{ $tpl->__('links.dashboard') }}</x-globals::forms.button>
    </div><br/><br/><br/><br/>

</div>
