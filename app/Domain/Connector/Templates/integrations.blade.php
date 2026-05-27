@extends($layout)

@section('content')

<div class="pageheader">
    <div class="pageicon"><span class="fa fa-plug"></span></div>
    <div class="pagetitle">
        <div class="row">
            <div class="col-lg-8">
                <h1>{!! __('headlines.integrations') !!}</h1>
            </div>
        </div>
    </div>
</div>

<div class="maincontent">
    <div class="maincontentinner">

        {!! $tpl->displayNotification() !!}

    </div>
</div>

@once
@push('scripts')
<script type="text/javascript">
   jQuery(document).ready(function() {
   });
</script>
@endpush
@endonce

@endsection
