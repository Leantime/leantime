@extends($layout)

@section('content')

<div class="pageheader">
    <div class="pagetitle">
        <h1>{!! __('headlines.update_database') !!}</h1>
    </div>
</div>
{!! $tpl->displayInlineNotification() !!}
<div class="regcontent" id="login">
    <p>{!! __('text.new_db_version') !!}</p><br />
    <form action="{{ BASE_URL }}/install/update" method="post" class="registrationForm">
        <input type="hidden" name="updateDB" value="1" />
        <p><input type="submit" name="updateAction" class="btn btn-primary" value="{{ __('buttons.update_now') }}" onClick="this.form.submit(); this.disabled=true; this.value='Updating…'; "/></p>
    </form>
</div>

@endsection
