@extends($layout)

@section('content')

<?php
$user = $tpl->get('user');
?>

<div class="pageheader">
    <div class="pageicon"><span class="fa fa-people-group"></span></div>
    <div class="pagetitle">
        <h5>{{ __("label.administration") }}</h5>
        <h1><h1>{{ __("headlines.delete_user") }}</h1></h1>
    </div>
</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">

        @displayNotification()

        <h4 class="widget widgettitle">{{ __("subtitles.delete") }}</h4>
        <div class="widgetcontent">

            <form method="post">
                <input type="hidden" name="<?=session("formTokenName")?>" value="<?=session("formTokenValue")?>" />
                <p>{{ __("text.confirm_user_deletion") }}</p><br />
                <input type="submit" value="{{ __("buttons.yes_delete") }}" name="del" class="button" />
                <a class="btn btn-primary" href="{{ BASE_URL }}/users/showAll">{{ __("buttons.back") }}</a>
            </form>


        </div>
    </div>
</div>
