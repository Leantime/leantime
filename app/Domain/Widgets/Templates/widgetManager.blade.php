<div class="" style="min-width:50%;">
    <h1>Widget Manager</h1>
    <p>Choose which widgets you would like to see on the dashboard.</p>
    <div class="row">
        @foreach($availableWidgets as $widget)
            <div class="col-md-3">
                <div class="projectBox tw-p-m tw-min-w-[250px]">
                    <h5>{{$widget->name}}</h5>
                    <p>{{ $widget->description }}</p>
                    <div class="right">
                        @if($widget->alwaysVisible == false)
                            <input type="checkbox" class="toggle" onclick="toggleWidgetVisibility('{{ $widget->id }}')" {{ isset($activeWidgets[$widget->id]) ? "checked='checked'" : "" }} />
                        @endif
                    </div>
                    <div class="clearall"></div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<script>
    function toggleWidgetVisibility(id){
        removeWidget(jQuery("#"+id).closest(".grid-stack-item")[0]);
    }

</script>
