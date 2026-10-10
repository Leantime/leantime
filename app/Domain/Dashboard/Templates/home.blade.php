@extends($layout)

@section('content')

<div class="maincontent" id="gridBoard" style="margin-top:0px; opacity:0;">

    {!! $tpl->displayNotification() !!}

    <div class="grid-stack">

        @foreach($dashboardGrid as $widget)

            @include('widgets::partials.widgetShell', ['widget' => $widget])

        @endforeach
    </div>
</div>

<script>

@dispatchEvent('scripts.afterOpen')

jQuery(document).ready(function() {

    leantime.widgetController.initGrid();

    @php(session(["usersettings.modals.homeDashboardTour" => 1]))

});
</script>

@endsection
