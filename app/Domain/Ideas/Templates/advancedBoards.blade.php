@extends($layout)

@section('content')

@php
    $allCanvas = $allCanvas ?? [];
    $canvasLabels = $canvasLabels ?? [];
    $canvasTitle = '';

    // All states >0 (<1 is archive)
    $numberofColumns = count($canvasLabels);
    $size = floor((100 / $numberofColumns) * 100) / 100;

    // get canvas title
    foreach ($allCanvas as $canvasRow) {
        if ($canvasRow['id'] == ($currentCanvas ?? '')) {
            $canvasTitle = $canvasRow['title'];
            break;
        }
    }
@endphp

<div class="pageheader">
    <div class="pageicon"><i class="far fa-lightbulb"></i></div>
    <div class="pagetitle">
        @if (count($allCanvas) > 0)
            <x-global::subjectSwitcher
                :parent="__('headlines.idea_management')"
                :current="$canvasTitle">
                @if ($login::userIsAtLeast($roles::$editor))
                    <li><a href="javascript:void(0)" class="addCanvasLink">{!! __('links.icon.create_new_board') !!}</a></li>
                @endif
                <li class="border"></li>
                @foreach ($allCanvas as $canvasRow)
                    <li><a href='{{ BASE_URL }}/ideas/showBoards/{{ $canvasRow['id'] }}'>{{ $tpl->escape($canvasRow['title']) }}</a></li>
                @endforeach
            </x-global::subjectSwitcher>
        @else
            <h1>{!! __('headlines.idea_management') !!}</h1>
        @endif
    </div>
    @if (count($allCanvas) > 0)
        <div class="pageheader-right">
            <x-global::actions.dropdown variant="header-menu">
                @if ($login::userIsAtLeast($roles::$editor))
                    <li><a href="#/ideas/boardDialog/{{ $currentCanvas }}">{!! __('links.icon.edit') !!}</a></li>
                    <li><a href="{{ BASE_URL }}/ideas/delCanvas/{{ $currentCanvas }}" class="delete">{!! __('links.icon.delete') !!}</a></li>
                @endif

            </x-global::actions.dropdown>
        </div>
    @endif
</div><!--pageheader-->

<div class="maincontent">
    @php
        $canvasSuffix = ! empty($currentCanvas) ? '/'.(int) $currentCanvas : '';
    @endphp
    <x-global::navigation.view-tabs :tabs="[
        ['url' => BASE_URL.'/ideas/showBoards'.$canvasSuffix, 'label' => __('buttons.idea_wall'), 'active' => false],
        ['url' => BASE_URL.'/ideas/advancedBoards'.$canvasSuffix, 'label' => __('buttons.idea_kanban'), 'active' => true],
    ]">
        @if ($login::userIsAtLeast($roles::$editor) && count($allCanvas) > 0)
            <x-slot:actions>
                <x-global::forms.button tag="a" link="#/ideas/ideaDialog?type=idea" contentRole="primary" id="customersegment"><span class="far fa-lightbulb"></span>{!! __('buttons.add_idea') !!}</x-global::forms.button>
            </x-slot:actions>
        @endif
    </x-global::navigation.view-tabs>

    <div class="maincontentinner">
        {!! $tpl->displayNotification() !!}

        @if (count($allCanvas) > 0)
            <div id="sortableIdeaKanban" class="sortableTicketList">

                <div class="row-fluid">

                    @foreach ($canvasLabels as $key => $statusRow)
                    <div class="column" style="width:{{ $size }}%;">

                        <h4 class="widgettitle title-primary">
                            @if ($login::userIsAtLeast($roles::$manager))
                                <a href="#/setting/editBoxLabel?module=idealabels&label={{ $key }}"
                                   class="editHeadline"><i class="fas fa-edit"></i></a>
                            @endif
                            {{ $statusRow['name'] }}
                        </h4>

                        <div class="contentInner status_{{ $key }}">

                            @foreach ($canvasItems as $row)
                                @if ($row['box'] == $key)
                                    <div class="ticketBox moveable" id="item_{{ $row['id'] }}">

                                        <div class="row">
                                            <div class="col-md-12">

                                                @if ($login::userIsAtLeast($roles::$editor))
                                                    <x-global::actions.dropdown style="float:right;">
                                                        <li class="nav-header">{!! __('subtitles.edit') !!}</li>
                                                        <li><a href="#/ideas/ideaDialog/{{ $row['id'] }}" class="" data="item_{{ $row['id'] }}"> {!! __('links.edit_canvas_item') !!}</a></li>
                                                        <li><a href="#/ideas/delCanvasItem/{{ $row['id'] }}" class="delete" data="item_{{ $row['id'] }}"> {!! __('links.delete_canvas_item') !!}</a></li>
                                                    </x-global::actions.dropdown>
                                                @endif

                                                <h4><a href="{{ BASE_URL }}/ideas/advancedBoards/#/ideas/ideaDialog/{{ $row['id'] }}" class=""
                                                       data="item_{{ $row['id'] }}">{{ $tpl->escape($row['description']) }}</a></h4>

                                                <div class="mainIdeaContent">

                                                    <div class="kanbanCardContent">

                                                        <div class="kanbanContent" style="margin-bottom: 20px">
                                                            {!! $tpl->escapeMinimal($row['data']) !!}
                                                        </div>

                                                    </div>
                                                </div>

                                                <div class="clearfix" style="padding-bottom: 8px;"></div>

                                                <x-global::elements.author-avatar class="dropRight" :user-id="$row['author']" :name="trim(($row['authorFirstname'] ?? '').' '.($row['authorLastname'] ?? ''))" />

                                                <div class="pull-right" style="margin-right:10px;">

                                                    <a href="#/ideas/ideaDialog/{{ $row['id'] }}"
                                                        data="item_{{ $row['id'] }}"
                                                        {!! $row['commentCount'] == 0 ? 'style="color: grey;"' : '' !!}>
                                                        <span class="fas fa-comments"></span></a> <small>{{ $row['commentCount'] }}</small>

                                                        @php $ideaTags = array_filter(array_map('trim', explode(',', (string) ($row['tags'] ?? '')))); @endphp
                                                        @if (count($ideaTags) > 0)
                                                            &nbsp;
                                                            <x-global::actions.dropdown variant="panel" as="span" class="dropdown" menu-as="ul" menu-class="pull-right">
                                                                <x-slot:trigger><i class="fa fa-tags" aria-hidden="true"></i> <small>{{ count($ideaTags) }}</small></x-slot:trigger>
                                                                <li style="padding:10px"><div class="tagsinput readonly">
                                                                    @foreach ($ideaTags as $tag)
                                                                        <span class="tag"><span>{{ $tag }}</span></span>
                                                                    @endforeach
                                                                </div></li>
                                                            </x-global::actions.dropdown>
                                                        @endif

                                                </div>

                                            </div>
                                        </div>

                                        @if ($row['milestoneHeadline'] != '')
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

                        </div>

                    </div>

                    @endforeach

                </div>
            </div>
            <div class="clearfix"></div>

        @else
            <br/><br/>
            <div class='center'>
                <div style='width:50%' class='svgContainer'>
                    {!! file_get_contents(ROOT . '/dist/images/svg/undraw_new_ideas_jdea.svg') !!}
                </div>

                <br/><h4>{!! __('headlines.have_an_idea') !!}</h4><br/>
                {!! __('subtitles.start_collecting_ideas') !!}<br/><br/>
                @if ($login::userIsAtLeast($roles::$editor))
                <x-global::forms.button tag="a" link="javascript:void(0);"
                   class="addCanvasLink" contentRole="primary">{!! __('buttons.start_new_idea_board') !!}</x-global::forms.button>
                @endif
            </div>

        @endif
        <!-- Modals -->

        <div class="modal fade bs-example-modal-lg" id="addCanvas">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form action="" method="post">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h4 class="modal-title">{!! __('headlines.start_new_idea_board') !!}</h4>
                        </div>
                        <div class="modal-body">
                            <label>{!! __('label.topic_idea_board') !!}</label>
                            <x-global::forms.text-input width="full" name="canvastitle"
                                   placeholder="{{ __('input.placeholders.name_for_idea_board') }}" />
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default"
                                    data-dismiss="modal">{!! __('buttons.close') !!}</button>
                            <x-global::forms.button tag="input" inputType="submit" contentRole="primary"
                                   :labelText="__('buttons.create_board')" name="newCanvas"/>
                        </div>
                    </form>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

        <div class="modal fade bs-example-modal-lg" id="editCanvas">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form action="" method="post">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h4 class="modal-title">{!! __('headlines.edit_board_name') !!}</h4>
                        </div>
                        <div class="modal-body">
                            <label>{!! __('label.title_idea_board') !!}</label>
                            <x-global::forms.text-input width="full" name="canvastitle" value="{{ $canvasTitle }}" />
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default"
                                    data-dismiss="modal">{!! __('buttons.close') !!}</button>
                            <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.save')"
                                   name="editCanvas"/>
                        </div>
                    </form>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

    </div>
</div>

@once
@push('scripts')
<script type="text/javascript">

    jQuery(document).ready(function () {

        leantime.ideasController.initBoardControlModal();
        leantime.ideasController.setKanbanHeights();

        @if ($login::userIsAtLeast($roles::$editor))
        var ideaStatusList = [@foreach ($canvasLabels as $key => $statusRow)'{{ $key }}',@endforeach];
            leantime.ideasController.initIdeaKanban(ideaStatusList);
        @else
            leantime.authController.makeInputReadonly(".maincontentinner");
        @endif

    });

</script>
@endpush
@endonce

@endsection
