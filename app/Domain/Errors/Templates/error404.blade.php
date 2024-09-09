@extends($layout)

@section('content')

<?php
?>

<div class="errortitle">

    <h4 class="animate0 fadeInUp">{{ __("headlines.page_not_found") }}</h4>
    <span class="animate1 bounceIn">4</span>
    <span class="animate2 bounceIn">0</span>
    <span class="animate3 bounceIn">4</span>
    <div class="errorbtns animate4 fadeInUp">
        <a onclick="history.back()" class="btn btn-default">{{ __("buttons.back") }}</a>
        <a href="{{ BASE_URL }}" class="btn btn-primary">{{ __("links.dashboard") }}</a>
    </div><br/><br/><br/><br/>

</div>
