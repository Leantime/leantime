{{-- One dashboard widget: the movable grid item with its header ⋮ menu and the lazy-loaded content.
     Rendered by the dashboard for every active widget and by Hxcontrollers\WidgetShell when the
     widget manager turns a widget on. --}}
@isset($widget)
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
        <div hx-get="{{ $widget->widgetUrl }}"
             hx-trigger="revealed"
             id="{{ $widget->id }}"
             class="tw-h-full"
             hx-swap="innerHTML">
            <x-global::loadingText type="{{ $widget->widgetLoadingIndicator }}" count="1" includeHeadline="true" />
        </div>
    </x-widgets::moveableWidget>
@endisset
