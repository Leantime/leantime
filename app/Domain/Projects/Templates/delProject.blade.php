@extends($layout)

@section('content')

<?php
$project = $tpl->get('project');
?>


<div class="pageheader">

    <div class="pageicon"><span class="fa fa-briefcase"></span></div>
    <div class="pagetitle">
        <h5>{{ __("label.administration") }}</h5>
        <h1><?php echo sprintf($tpl->__('headlines.delete_project_x'), $project['name']); ?></h1>
    </div>
</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">

        @displayNotification()

        <h4 class="widget widgettitle">{!! __("subtitles.delete") !!}</h4>
        <div class="widgetcontent">

            <form method="post">
                <p>{{ __("text.confirm_project_deletion") }}</p><br />
                <x-global::forms.button type="submit" name="del" class="button">
                    {{ __('buttons.yes_delete') }}
                </x-global::forms.button>
                
                <x-global::forms.button tag="a" href="{{ BASE_URL }}/projects/showProject/{{ $project['id'] }}">
                    {{ __('buttons.back') }}
                </x-global::forms.button>
            </form>

        </div>


    </div>
</div>

@endsection
