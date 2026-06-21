@extends($layout)
@section('content')

<div class="pageheader">
    <div class="pageicon"><span class="fa {{ $tpl->getModulePicture() }}"></span></div>
    <div class="pagetitle">
        <h5>{!! __('label.administration') !!}</h5>
        <h1><h1>{!! __('headlines.delete_user') !!}</h1></h1>
    </div>
</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">

        {!! $tpl->displayNotification() !!}

        <h4 class="widget widgettitle">{!! __('subtitles.delete') !!}</h4>
        <div class="widgetcontent">

            <form method="post">
                <input type="hidden" name="{{ session('formTokenName') }}" value="{{ session('formTokenValue') }}" />
                <p>{!! __('text.confirm_user_deletion') !!}</p><br />
                <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.yes_delete')" name="del" />
                <x-global::forms.button tag="a" contentRole="tertiary" link="{{ BASE_URL }}/users/showAll">{!! __('buttons.back') !!}</x-global::forms.button>
            </form>


        </div>
    </div>
</div>

@endsection
