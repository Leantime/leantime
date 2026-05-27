@extends($layout)

@section('content')

<div class="pageheader">
    <div class="pagetitle">
        <h1>{!! __('headlines.installation') !!}</h1>
    </div>

</div>
<div class="regcontent" id="login">
    <p>{!! __('text.this_script_will_set_up_leantime') !!}</p><br />

    {!! $tpl->displayInlineNotification() !!}

    <form action="{{ BASE_URL }}/install" method="post" class="registrationForm">
        <h3 class="subtitle">{!! __('subtitles.login_info') !!}</h3>
        <input type="email" name="email" class="form-control" placeholder="{{ __('label.email') }}" value=""/><br />
        <br /><br />
        <h3 class="subtitle">{!! __('subtitles.user_info') !!}</h3>
        <input type="text" name="firstname" class="form-control" placeholder="{{ __('label.firstname') }}" value=""/><br />
        <input type="text" name="lastname" class="form-control" placeholder="{{ __('label.lastname') }}" value=""/>
        <input type="text" name="company" class="form-control" placeholder="{{ __('label.company_name') }}" value=""/>
        <br /><br />
        <input type="hidden" name="install" value="Install" />
        <p><input type="submit" name="installAction" class="btn btn-primary" value="{{ __('buttons.install') }}" onClick="this.form.submit(); this.disabled=true; this.value='{{ __('buttons.install') }}'; "/></p>
    </form>

</div>

@endsection
