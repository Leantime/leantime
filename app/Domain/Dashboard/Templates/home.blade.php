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
                     hx-target="this"
                     hx-swap="innerHTML"
                     id="{{ $widget->id }}"
                     class="tw:h-full"
                     aria-live="polite">
                    <x-globals::feedback.skeleton type="{{ $widget->widgetLoadingIndicator }}" count="1" includeHeadline="true" />
                </div>
            </x-widgets::moveableWidget>

        @endforeach
    </div>
</div>

<script>

@dispatchEvent('scripts.afterOpen')

jQuery(document).ready(function() {

    leantime.widgetController.initGrid();

    @php(session(["usersettings.modals.homeDashboardTour" => 1]))

});

// Promote .widget-slot-actions from content into the stickyHeader
// so action icons align with the three-dots menu.
function promoteWidgetActions(root) {
    if (!root) root = document;
    root.querySelectorAll('.widget-slot-actions').forEach(function (slotActions) {
        if (slotActions.closest('.stickyHeader')) return; // already promoted
        var widgetEl = slotActions.closest('.grid-stack-item-content');
        if (!widgetEl) return;
        var headerTarget = widgetEl.querySelector('.widget-header-actions');
        if (!headerTarget) return;
        headerTarget.innerHTML = '';
        headerTarget.appendChild(slotActions);
        slotActions.style.display = 'flex';
        slotActions.style.alignItems = 'center';
        slotActions.style.gap = '2px';
    });
}

// Catch future HTMX swaps (widget refreshes, initial loads).
document.body.addEventListener('htmx:afterSwap', function () {
    promoteWidgetActions();
});

// Sweep for any widgets that loaded before this listener was ready.
document.addEventListener('DOMContentLoaded', function () {
    setTimeout(promoteWidgetActions, 500);
});
</script>

@endsection
