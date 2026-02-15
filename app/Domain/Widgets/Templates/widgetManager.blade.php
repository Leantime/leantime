<div class="" style="min-width:50%;">
    <h1>{{ __("headlines.widget_manager") }}</h1>
    <a href="{{ BASE_URL }}/dashboard/home?resetDashboard=true" class="btn btn-outline pull-right" style="margin-bottom:10px;"><i class="fa-solid fa-arrow-rotate-left"></i> Reset Dashboard</a>
    <p>{{ __("text.choose_widgets") }}</p>
    <br />
    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:15px;">
        @foreach($availableWidgets as $widgetId => $widget)
            @if($widget->alwaysVisible !== true)
                @php( $widget->name = __($widget->name))
                @php( $widget->description = __($widget->description))
                <div class="projectBox tw:p-m @if(in_array($widgetId, array_keys($newWidgets)) && !isset($activeWidgets[$widgetId])) newWidget @endif">
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
