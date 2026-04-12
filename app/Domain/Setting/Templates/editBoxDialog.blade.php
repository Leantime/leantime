@extends($layout)
@section('content')

<h4 class="widgettitle title-light">{!! __('headlines.edit_label') !!}</h4>

{!! $tpl->displayNotification() !!}

<form class="formModal" method="post" action="{{ BASE_URL }}/setting/editBoxLabel?module={{ $_GET['module'] }}&label={{ $_GET['label'] }}">

    <label>{!! __('label.label') !!}</label>
    <input type="text" name="newLabel" value="{{ $currentLabel }}" /><br />

    <div class="row">
        <div class="col-md-6">
            <input type="submit" value="{{ __('buttons.save') }}"/>
        </div>

    </div>

</form>

@endsection
