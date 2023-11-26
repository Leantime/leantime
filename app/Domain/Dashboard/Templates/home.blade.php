@extends($layout)

@section('content')

<x-global::pageheader :icon="'fa fa-home'">
    <h5>{{ $_SESSION['currentProjectClient'] }}</h5>
    <h1>{!! __('headlines.home') !!}</h1>

</x-global::pageheader>

<div class="maincontent">

    {!! $tpl->displayNotification() !!}

    <div class="grid-stack">

        @foreach($dashboardGrid as $widget)

            <x-widgets::moveableWidget
                gs-x="{{ $widget['x'] }}"
                gs-y="{{ $widget['y'] }}"
                gs-h="{{ $widget['h'] ?? $widget['minh'] }}"
                gs-w="{{ $widget['w'] ?? $widget['minW'] }}"
            >
                <div hx-get="{{$widget['hxget']}}" hx-trigger="{{$widget['hxtrigger']}}">
                    <x-global::loadingText type="card" count="1" includeHeadline="true" />
                </div>
            </x-widgets::moveableWidget>

        @endforeach

        @if($dashboardGrid == '')

            <x-widgets::moveableWidget gs-h="4" gs-w="4">
                 <div hx-get="{{ BASE_URL }}/widgets/pomodoro/get" hx-trigger="load" >
                     <x-global::loadingText type="text" count="1" includeHeadline="true"/>

                 </div>
            </x-widgets::moveableWidget>

            <x-widgets::moveableWidget gs-h="1" gs-w="8">
                <div hx-get="{{ BASE_URL }}/widgets/welcome/get" hx-trigger="load" >
                    <x-global::loadingText type="text" count="1" includeHeadline="true"/>
                </div>
            </x-widgets::moveableWidget>

            <x-widgets::moveableWidget gs-h="2" gs-w="4">
                <div hx-get="{{ BASE_URL }}/widgets/calendar/get" hx-trigger="load" >
                    <x-global::loadingText type="text" count="1" includeHeadline="true"/>
                </div>
            </x-widgets::moveableWidget>

            <x-widgets::moveableWidget gs-w="8" gs-h="6">
                <div hx-get="{{ BASE_URL }}/widgets/myToDos/get" hx-trigger="load" >
                    <x-global::loadingText type="text" count="1" includeHeadline="true" />
                </div>
            </x-widgets::moveableWidget>
        @endif

    </div>
</div>

<script>
@dispatchEvent('scripts.afterOpen')

jQuery(document).ready(function() {

    let grid = GridStack.init({
        margin: 10,
        handle: ".grid-handler-top",
        minRow: 4, // don't let it collapse when empty
        cellHeight: '100px',
    });

    grid.on('dragstop', function(event, item) {
        saveGrid();
    });

    grid.on('resizestop', function(Event, item) {

        saveGrid();
    });


    // 2.x method
    function saveGrid() {


        let items = grid.save();

        items.forEach(function(item) {
            //get hx links
            let htmxElement = jQuery(item.content).find("[hx-get]").first();

            item.hxget = htmxElement.attr("hx-get");
            item.hxtrigger = htmxElement.attr("hx-trigger");

            if(item.x == undefined) {
                item.x = 0;
            }

            if(item.y == undefined) {
                item.y = 0;
            }

            if(item.w == undefined) {
                item.w = 2;
            }

            if(item.h == undefined) {
                item.h = 2;
            }

            item.content = '';
        });

        console.log(items);

        GridStack.Utils.sort(items);

        jQuery.post("{{ BASE_URL }}/dashboard/home",
            {
                action: "saveGrid",
                data: items
            },
            function(data, status){
                console.log(data);
            });
    }

    function removeWidget(el) {
        // TEST removing from DOM first like Angular/React/Vue would do
        el.remove();
        grid.removeWidget(el, true);
        saveGrid();
    }

    jQuery(".grid-stack-item").each(function(){
        jQuery(this).find(".removeWidget").click(function(){
            removeWidget(jQuery(this).closest(".grid-stack-item")[0]);
        });

    })



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
