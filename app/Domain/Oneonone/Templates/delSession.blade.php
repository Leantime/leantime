@extends($layout)
@section('content')

<h4 class="widgettitle title-light">{!! __('subtitles.delete') !!}</h4>

<form method="post" action="{{ BASE_URL.'/oneonone/delSession/'.$session['id'] }}">
    @if(isset($csrf_token))
        <input type="hidden" name="csrf_token" value="{{ $csrf_token }}">
    @endif
    <p>{{ __('text.oneonone.confirm_delete') }}</p><br/>
    <input type="submit" value="{{ __('buttons.yes_delete') }}" name="del" class="button btn btn-primary"/>
    <a class="btn btn-secondary" href="{{ BASE_URL.'/oneonone/showSession/'.$session['id'] }}">{{ __('buttons.back') }}</a>
</form>

@endsection
