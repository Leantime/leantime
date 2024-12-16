@extends($layout)

@section('content')

<div class="maincontent" id="gridBoard" style="margin-top:0px; opacity:0;">

    @displayNotification()

    <div class="grid-stack">

        @foreach($dashboardGrid as $widget)

            <x-widgets::moveableWidget
                gs-x="{{ $widget->gridX }}"
                gs-y="{{ $widget->gridY }}"
                gs-h="{{ $widget->gridHeight }}"
                gs-w="{{ $widget->gridWidth }}"
                gs-min-w="{{ $widget->gridMinWidth }}"
                gs-min-h="{{ $widget->gridMinHeight }}"
                isNew="{{ isset($widget->isNew) ? 'true' : 'false' }}"
                background="{{ $widget->widgetBackground }}"
                noTitle="{{ $widget->noTitle }}"
                name="{{ $widget->name }}"
                alwaysVisible="{{ $widget->alwaysVisible }}">

                <div hx-get="{{$widget->widgetUrl }}"
                     hx-trigger="revealed"
                     id="{{ $widget->id }}"
                    hx-swap="#{{ $widget->id }} transition:true">
                    <x-global::elements.loadingText type="{{ $widget->widgetLoadingIndicator }}" count="1" includeHeadline="true" />
                </div>

            </x-widgets::moveableWidget>

        @endforeach
    </div>
</div>

<script type="module">

    leantime.moduleLoader.load("@mix('/js/Domain/Widgets/Js/widgetController')").then(()=>
        widgetController.initGrid()
    );


    @if($completedOnboarding === false)
        leantime.moduleLoader.load("@mix('/js/Domain/Help/Js/helperController')").then(()=>
            helperController.firstLoginModal()
        );
    @endif

    @php(session(["usersettings.modals.homeDashboardTour" => 1]));

</script>

@endsection
