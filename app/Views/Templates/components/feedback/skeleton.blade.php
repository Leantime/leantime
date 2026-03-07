@props([
    'count' => 1,
    'includeHeadline' => false,
    'type' => 'text'
])

<div role="status" aria-live="polite">
    <span class="sr-only">{{ __('label.loading') }}</span>
</div>

@if($includeHeadline == 'true')
    <div class="loading-text" aria-hidden="true">
        <p style="width:40%">Loading...</p>
        <br />
    </div>
    <br />
@endIf

@if($type == 'card')
    @for ($i = 0; $i < $count; $i++)
        <div class="loading-text tw:w-full" aria-hidden="true">
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
        <div class="loading-text" aria-hidden="true">
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
        <div class="loading-text" aria-hidden="true">
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
        <div class="loading-text" aria-hidden="true">
            <p style="width:40%">Loading...</p>
            <br />
        </div>
    @endfor
@endif

@if($type == 'project')
    @for ($i = 0; $i < $count; $i++)
        <div class="loading-text" aria-hidden="true">
            <p style="margin-left:10px; margin-right:10px; width:30px; height:30px; float:left;">Loading...</p>
            <p style="width:200px; margin-left:50px;"></p>
            <br />
        </div>
    @endfor
@endif

@if($type == 'plugincard')
    @for ($i = 0; $i < $count; $i++)
        <div class="loading-text" aria-hidden="true" style="margin-bottom: 12px;">
            <div style="display: flex; align-items: flex-start; gap: 20px; padding: 20px; background: var(--kanban-card-bg); border-radius: var(--box-radius);">
                <p style="width: 75px; height: 75px; flex-shrink: 0; border-radius: var(--box-radius-small);">Loading...</p>
                <div style="flex: 1;">
                    <p style="width: 35%; margin-bottom: 8px;">Loading...</p>
                    <p style="width: 20%; margin-bottom: 8px;">Loading...</p>
                    <p style="width: 80%;">Loading...</p>
                </div>
                <p style="width: 90px; flex-shrink: 0;">Loading...</p>
            </div>
        </div>
    @endfor
@endif
