@extends($layout)

@section('content')

<div class="maincontent" id="gridBoard" style="margin-top:0px; opacity:0;">

    {!! $tpl->displayNotification() !!}

    <div class="grid-stack">

        @foreach($dashboardGrid as $widget)

            <x-widgets::moveableWidget
                gs-x="{{ $widget->gridX }}"
                gs-y="{{ $widget->gridY }}"
                gs-h="{{ $widget->gridHeight }}"
                gs-w="{{ $widget->gridWidth }}"
                gs-min-w="{{ $widget->gridMinWidth }}"
                gs-min-h="{{ $widget->gridMinHeight }}"
                background="{{ $widget->widgetBackground }}"
                noTitle="{{ $widget->noTitle }}"
                name="{{ $widget->name }}"
                alwaysVisible="{{ $widget->alwaysVisible }}">
                <div hx-get="{{$widget->widgetUrl }}"
                     hx-trigger="{{$widget->widgetTrigger }}"
                     id="{{ $widget->id }}"
                    hx-swap="#{{ $widget->id }} transition:true">
                    <x-global::loadingText type="{{ $widget->widgetLoadingIndicator }}" count="1" includeHeadline="true" />
                </div>
            </x-widgets::moveableWidget>

        @endforeach
    </div>
</div>

<script>
@dispatchEvent('scripts.afterOpen')

jQuery(document).ready(function() {

    leantime.widgetController.initGrid();

    @if($completedOnboarding === false)
        leantime.helperController.firstLoginModal();
    @endif

    @if($completedOnboarding == "1" && (isset($_SESSION['userdata']['settings']["modals"]["dashboard"]) === false || $_SESSION['userdata']['settings']["modals"]["dashboard"] == 0))
        leantime.helperController.showHelperModal("dashboard", 500, 700);

        @if(!isset($_SESSION['userdata']['settings']["modals"]))
            @php($_SESSION['userdata']['settings']["modals"] = array())
        @endif

        @if(!isset($_SESSION['userdata']['settings']["modals"]["dashboard"]))
            @php($_SESSION['userdata']['settings']["modals"]["dashboard"] = 1)
        @endif
    @endif
});
</script>

@endsection
