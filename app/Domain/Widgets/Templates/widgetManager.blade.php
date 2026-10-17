<div class="" style="min-width:50%;">
    <h1>{{ __("headlines.widget_manager") }}</h1>
    <x-global::forms.button tag="a" contentRole="secondary" class="pull-right" link="{{ BASE_URL }}/dashboard/home?resetDashboard=true" style="margin-bottom:10px;"><i class="fa-solid fa-arrow-rotate-left"></i> {{ __('buttons.reset_dashboard') }}</x-global::forms.button>
    <p>{{ __("text.choose_widgets") }}</p>
    <br />
    <div class="row">
        @foreach($availableWidgets as $widgetId => $widget)
            @if($widget->alwaysVisible !== true)
                @php( $widget->name = __($widget->name))
                @php( $widget->description = __($widget->description))
                <div class="col-md-4">
                    <div class="projectBox tw-p-m tw-min-w-[250px] @if(in_array($widgetId, array_keys($newWidgets)) && !isset($activeWidgets[$widgetId])) newWidget @endif">
                        <h5>{{ $widget->name }}</h5>
                        <p>{!! $widget->description !!} </p>
                        <div class="right">
                            @if($widget->alwaysVisible == false)
                                <x-global::forms.checkbox variant="toggle" :id="'widget-toggle-' . $widget->id"
                                    :checked="isset($activeWidgets[$widget->id])"
                                    :data-is-new="! empty($activeWidgets[$widget->id]->isNew) ? 'true' : null"
                                    :aria-label="$widget->name"
                                    onclick="leantime.widgetController.toggleWidgetVisibility('{{ $widget->id }}', this, {{ json_encode($widget) }})" />
                            @endif
                        </div>
                        <div class="clearall"></div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
    <div class="clear"></div>
</div>
