<div class="tw:min-w-[50%]">
    <h1>{{ __("headlines.widget_manager") }}</h1>
    <x-globals::forms.button link="{{ BASE_URL }}/dashboard/home?resetDashboard=true" contentRole="secondary" :outline="true" leadingVisual="undo" class="pull-right tw:mb-2">Reset Dashboard</x-globals::forms.button>
    <p>{{ __("text.choose_widgets") }}</p>
    <br />
    <div class="tw:grid tw:grid-cols-[repeat(auto-fill,minmax(220px,1fr))] tw:gap-4">
        @foreach($availableWidgets as $widgetId => $widget)
            @if($widget->alwaysVisible !== true)
                @php( $widget->name = __($widget->name))
                @php( $widget->description = __($widget->description))
                <div class="tw:p-4 @if(in_array($widgetId, array_keys($newWidgets)) && !isset($activeWidgets[$widgetId])) newWidget @endif">
                    <h5>{{ $widget->name }}</h5>
                    <p>{!! $widget->description !!} </p>
                    <div class="right">
                        @if($widget->alwaysVisible == false)
                            <input
                                type="checkbox"
                                class="toggle"
                                id="widget-toggle-{{ $widget->id }}"
                                onclick="leantime.widgetController.toggleWidgetVisibility('{{ $widget->id }}', this, {{ json_encode($widget) }})"
                                @if(isset($activeWidgets[$widget->id]))
                                    checked='checked'
                                    @if(isset($activeWidgets[$widget->id]->isNew) && $activeWidgets[$widget->id]->isNew)
                                        data-is-new="true"
                                    @endif
                                @endif
                            />
                            <label for="widget-toggle-{{ $widget->id }}"></label>
                        @endif
                    </div>
                    <div class="clearall"></div>
                </div>
            @endif
        @endforeach
    </div>
    <div class="clear"></div>
</div>
