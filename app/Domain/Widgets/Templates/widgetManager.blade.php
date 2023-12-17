<div class="" style="min-width:50%;">
    <h1>{{ __("headlines.widget_manager") }}</h1>
    <p>{{ __("text.choose_widgets") }}</p>
    <br />
    <div class="row">
        @foreach($availableWidgets as $widget)
            <div class="col-md-4">
                <div class="projectBox tw-p-m tw-min-w-[250px]">
                    <h5>{{ __($widget->name) }}</h5>
                    <p>{{ __($widget->description) }}</p>
                    <div class="right">
                        @if($widget->alwaysVisible == false)
                            <input type="checkbox" class="toggle" onclick="leantime.widgetController.toggleWidgetVisibility('{{ $widget->id }}', this, {{ json_encode($widget) }})" {{ isset($activeWidgets[$widget->id]) ? "checked='checked'" : "" }} />
                        @endif
                    </div>
                    <div class="clearall"></div>
                </div>
            </div>
        @endforeach
    </div>
</div>
