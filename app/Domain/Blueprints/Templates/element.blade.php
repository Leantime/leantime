<h4 class="widgettitle title-primary">
    @if(isset($canvasTypes[$elementName]['icon']))
        <i class="fas {{ $canvasTypes[$elementName]['icon'] }}"></i>
    @endif
    {{ $canvasTypes[$elementName]['title'] }}
</h4>
<div class="contentInner even status_{{ $elementName }}"
     {!! isset($canvasTypes[$elementName]['color']) ? 'style="background: ' . $canvasTypes[$elementName]['color'] . ';"' : '' !!}>

    @foreach($canvasItems as $row)
        @php
            $filterStatus = $filter['status'] ?? 'all';
            $filterRelates = $filter['relates'] ?? 'all';
        @endphp

        @if($row['box'] === $elementName && ($filterStatus == 'all' || $filterStatus == $row['status']) && ($filterRelates == 'all' || $filterRelates == $row['relates']))
            @php
                // Use the module-scoped count already computed by getCanvasItemsById
                // (avoids an unscoped per-item query that miscounts across modules).
                $nbcomments = (int) ($row['commentCount'] ?? 0);
            @endphp

            <div class="ticketBox" id="item_{{ $row['id'] }}">
                <div class="row">
                    <div class="col-md-12">
                        <div class="inlineDropDownContainer" style="float:right;">

                            @if($login::userIsAtLeast($roles::$editor))
                                <a href="javascript:void(0)" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                                    <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                </a>
                            @endif

                            @if($login::userIsAtLeast($roles::$editor))
                                &nbsp;&nbsp;&nbsp;
                                <ul class="dropdown-menu">
                                    <li class="nav-header">{!! __('subtitles.edit') !!}</li>
                                    <li><a href="#/blueprints/{{ $canvasSlug }}/editCanvasItem/{{ $row['id'] }}"
                                           data="item_{{ $row['id'] }}"> {!! __('links.edit_canvas_item') !!}</a></li>
                                    <li><a href="#/blueprints/{{ $canvasSlug }}/delCanvasItem/{{ $row['id'] }}"
                                           class="delete"
                                           data="item_{{ $row['id'] }}"> {!! __('links.delete_canvas_item') !!}</a></li>
                                </ul>
                            @endif
                        </div>

                        <h4><a href="#/blueprints/{{ $canvasSlug }}/editCanvasItem/{{ $row['id'] }}"
                               data="item_{{ $row['id'] }}">{{ $row['description'] }}</a></h4>

                        @if($row['conclusion'] != '')
                            <small>{!! $tpl->escapeMinimal($row['conclusion']) !!}</small>
                        @endif

                        <div class="clearfix" style="padding-bottom: 8px;"></div>

                        @if(! empty($statusLabels))
                            <x-blueprints::chip-label type="status" adapter="canvas" :canvas-type="$canvasType" :item-id="$row['id']" :value="$row['status'] ?? null" :labels="$statusLabels" />
                        @endif

                        @if(! empty($relatesLabels))
                            <x-blueprints::chip-label type="relates" adapter="canvas" :canvas-type="$canvasType" :item-id="$row['id']" :value="$row['relates'] ?? null" :labels="$relatesLabels" />
                        @endif

                        <x-global::elements.author-avatar class="dropRight" :user-id="$row['author']" :name="trim(($row['authorFirstname'] ?? '').' '.($row['authorLastname'] ?? ''))" />
                        <div class="pull-right" style="margin-right:10px;">
                            <span class="fas fa-comments"></span> <small>{{ $nbcomments }}</small>
                        </div>
                    </div>
                </div>

                @if($row['milestoneHeadline'] != '')
                    <br/>
                    <div hx-trigger="load"
                         hx-indicator=".htmx-indicator"
                         hx-get="{{ BASE_URL }}/hx/tickets/milestones/showCard?milestoneId={{ $row['milestoneId'] }}">
                        <div class="htmx-indicator">
                            {!! __('label.loading_milestone') !!}
                        </div>
                    </div>
                @endif
            </div>
        @endif
    @endforeach
    <br />
    @if($login::userIsAtLeast($roles::$editor))
        <a href="#/blueprints/{{ $canvasSlug }}/editCanvasItem?type={{ $elementName }}"
           class="" id="{{ $elementName }}"
           style="padding-bottom: 10px;">{!! __('links.add_new_canvas_item') !!}</a>
    @endif
</div>
