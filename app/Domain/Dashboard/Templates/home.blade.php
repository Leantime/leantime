@extends($layout)

@section('content')

<div class="maincontent" style="margin-top:0px">

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

            >
                <div hx-get="{{$widget->widgetUrl }}" hx-trigger="{{$widget->widgetTrigger }}" id="{{ $widget->id }}">
                    <x-global::loadingText type="{{ $widget->widgetLoadingIndicator }}" count="1" includeHeadline="true" />
                </div>
            </x-widgets::moveableWidget>

        @endforeach
    </div>
</div>

<script>
@dispatchEvent('scripts.afterOpen')

jQuery(document).ready(function() {

    let grid = GridStack.init({
        margin: 5,
        handle: ".grid-handler-top",
        minRow: 2, // don't let it collapse when empty
        cellHeight: '30px',
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

            item.id = htmxElement.attr("id");
            item.widgetUrl = htmxElement.attr("hx-get");
            item.widgetTrigger = htmxElement.attr("hx-trigger");

            if(item.x == undefined) {
                item.x = 0;
            }
            item.gridX = item.x;

            if(item.y == undefined) {
                item.y = 0;
            }
            item.gridY = item.y;

            if(item.w == undefined) {
                item.w = 2;
            }
            item.gridWidth = item.w;

            if(item.h == undefined) {
                item.h = 2;
            }
            item.gridHeight = item.h;

            item.content = '';

            console.log(item);
        });

        GridStack.Utils.sort(items);

        jQuery.post("{{ BASE_URL }}/widgets/widgetManager",
            {
                action: "saveGrid",
                data: items
            },
            function(data, status){

            });
    }

    function removeWidget(el) {
        // TEST removing from DOM first like Angular/React/Vue would do
        el.remove();
        grid.removeWidget(el, true);
        saveGrid();
    }

    function resizeWidget(el) {

        console.log(jQuery(el).find(".grid-stack-item-content").css("height"));
        grid.resizeToContent(el, false);
        saveGrid();
    }

    jQuery(".grid-stack-item").each(function(){
        jQuery(this).find(".removeWidget").click(function(){
            removeWidget(jQuery(this).closest(".grid-stack-item")[0]);
        });
        jQuery(this).find(".fitContent").click(function(){
            console.log(jQuery(this).closest(".grid-stack-item")[0])
            resizeWidget(jQuery(this).closest(".grid-stack-item")[0]);
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
