<div class="" style="min-width:50%;">
    <h1>{{ __("headlines.widget_manager") }}</h1>
    <a href="{{ BASE_URL }}/dashboard/home?resetDashboard=true" class="btn btn-outline pull-right" style="margin-bottom:10px;"><i class="fa-solid fa-arrow-rotate-left"></i> Reset Dashboard</a>
    <p>{{ __("text.choose_widgets") }}</p>
    <br />
    <div class="row">
        @foreach($availableWidgets as $widget)
            @if($widget->alwaysVisible !== true)
                <div class="col-md-4">
                    <div class="projectBox p-m min-w-[250px]">
                        <h5>{{ __($widget->name) }}</h5>
                        <p>{{ __($widget->description) }}</p>
                        <div class="right">
                            @if($widget->alwaysVisible == false)
                                <x-global::forms.checkbox
                                    name="widgetToggle"
                                    class="toggle"
                                    onclick="leantime.widgetController.toggleWidgetVisibility('{{ $widget->id }}', this, {{ json_encode($widget) }})"
                                    :checked="isset($activeWidgets[$widget->id])"
                                />
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
