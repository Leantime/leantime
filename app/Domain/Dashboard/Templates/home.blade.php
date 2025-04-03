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
                isNew="{{ isset($widget->isNew) ? 'true' : 'false' }}"
                background="{{ $widget->widgetBackground }}"
                noTitle="{{ $widget->noTitle }}"
                name="{{ $widget->name }}"
                :fixed="(empty($widget->fixed) ? false : true )"
                alwaysVisible="{{ $widget->alwaysVisible }}"
                id="widget_wrapper_{{ $widget->id }}"
            >
                <div hx-get="{{$widget->widgetUrl }}"
                     hx-trigger="revealed"
                     id="{{ $widget->id }}"
                    hx-swap="#{{ $widget->id }}">
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

    @php(session(["usersettings.modals.homeDashboardTour" => 1]));

});
</script>

@endsection
