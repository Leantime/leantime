@extends($layout)

@section('content')

<div class="pageheader">
    <div class="pageicon"><i class="fa-solid fa-key"></i></div>
    <div class="pagetitle">
        <h5>{!! __('label.administration') !!}</h5>
        <h1>{!! __('headlines.delete_key') !!}</h1>
    </div>
</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">

        {!! $tpl->displayNotification() !!}

        <h5 class="subtitle">{!! __('subtitles.delete_key') !!}</h5>

            <form method="post">
                <input type="hidden" name="{{ session('formTokenName') }}" value="{{ session('formTokenValue') }}" />
                <p>{!! __('text.confirm_key_deletion') !!}</p><br />
                <input type="submit" value="{{ __('buttons.yes_delete') }}" name="del" class="button" />
                <a class="btn btn-primary" href="{{ BASE_URL }}/setting/editCompanySettings/#apiKeys">{!! __('buttons.back') !!}</a>
            </form>

    </div>
</div>

@endsection
