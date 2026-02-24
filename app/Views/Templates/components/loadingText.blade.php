@props([
    'count' => 1,
    'includeHeadline' => false,
    'type' => 'text'
])

@if($includeHeadline == 'true')
    <div class="loading-text">
        <p style="width:40%">Loading...</p>
        <br />
    </div>
    <br />
@endIf

@if($type == 'card')
    @for ($i = 0; $i < $count; $i++)
        <div class="loading-text tw:w-full">
            <div class="row" style="margin-bottom:var(--base-spacing-l);">
                <div class="col-md-6">
                    <p style="width:30%">Loading...</p>
                    <p style="width:60%">Loading...</p>
                    <p style="width:20%">Loading...</p>
                </div>
                <div class="col-md-6 align-right">
                    <p style="width:5%" class="pull-right">Loading...</p><div class="clearall"></div>
                    <div class="clearall"></div><br />
                    <p style="width:20%" class="pull-right tw:ml-sm">Loading...</p>&nbsp;<p style="width:25%" class="pull-right tw:ml-sm">Loading...</p>&nbsp;<p style="width:10%" class="pull-right tw:ml-sm">Loading...</p>
                </div>
            </div>
        </div>
    @endfor
@endif

@if($type == 'text')
    @for ($i = 0; $i < $count; $i++)
        <div class="loading-text">
            <p style="width:90%">Loading...</p>
            <p style="width:90%">Loading...</p>
            <br />
            <p style="width:60%">Loading...</p>
            <p style="width:65%">Loading...</p>
            <p style="width:55%">Loading...</p>
            <p style="width:50%">Loading...</p>
            <p style="width:20%">Loading...</p>
            <br />
            <p style="width:90%">Loading...</p>
            <p style="width:90%">Loading...</p>
        </div>
    @endfor
@endif

@if($type == 'longtext')
    @for ($i = 0; $i < $count; $i++)
        <div class="loading-text">
            <p style="width:90%">Loading...</p>
            <p style="width:90%">Loading...</p>
            <br />
            <p style="width:60%">Loading...</p>
            <p style="width:65%">Loading...</p>
            <p style="width:55%">Loading...</p>
            <p style="width:50%">Loading...</p>
            <p style="width:20%">Loading...</p>
            <br />
            <p style="width:90%">Loading...</p>
            <p style="width:90%">Loading...</p>
            <p style="width:90%">Loading...</p>
            <p style="width:90%">Loading...</p>
            <br />
            <p style="width:60%">Loading...</p>
            <p style="width:65%">Loading...</p>
            <p style="width:55%">Loading...</p>
            <p style="width:50%">Loading...</p>
            <p style="width:20%">Loading...</p>
            <br />
            <p style="width:90%">Loading...</p>
            <p style="width:90%">Loading...</p>
            <p style="width:90%">Loading...</p>
            <p style="width:90%">Loading...</p>
            <br />
            <p style="width:60%">Loading...</p>
            <p style="width:65%">Loading...</p>
            <p style="width:55%">Loading...</p>
            <p style="width:50%">Loading...</p>
            <p style="width:20%">Loading...</p>
            <br />
            <p style="width:90%">Loading...</p>
            <p style="width:90%">Loading...</p>
        </div>
    @endfor
@endif

@if($type == 'line')
    @for ($i = 0; $i < $count; $i++)
        <div class="loading-text">
            <p style="width:40%">Loading...</p>
            <br />
        </div>
    @endfor
@endif

@if($type == 'project')
    @for ($i = 0; $i < $count; $i++)
        <div class="loading-text">
            <p style="margin-left:10px; margin-right:10px; width:30px; height:30px; float:left;">Loading...</p>
            <p style="width:200px; margin-left:50px;"></p>
            <br />
        </div>
    @endfor
@endif

@if($type == 'plugincard')
    <div class="row">
    @for ($i = 0; $i < $count; $i++)
        <div class="col-md-4">
            <div class="loading-text">
                <div style="margin-bottom:var(--base-spacing-l);">
                    <p style="width:100%; height:80px;">Loading...</p>
                </div>
                <div class="row" style="margin-bottom:var(--base-spacing-l);">
                    <div class="col-md-6">
                        <p style="width:60%">Loading...</p>
                        <p style="width:20%">Loading...</p>
                    </div>
                    <div class="col-md-6 align-right">
                        <p style="width:5%" class="pull-right">Loading...</p><div class="clearall"></div>
                        <div class="clearall"></div><br />
                        <p style="width:20%" class="pull-right tw:ml-sm">Loading...</p>&nbsp;<p style="width:25%" class="pull-right tw:ml-sm">Loading...</p>&nbsp;<p style="width:10%" class="pull-right tw:ml-sm">Loading...</p>
                    </div>
                </div>
            </div>
        </div>
    @endfor
    </div>
@endif


