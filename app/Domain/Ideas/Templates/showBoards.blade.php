@extends($layout)

@section('content')

@php
    $allCanvas = $allCanvas ?? [];
    $canvasTitle = '';
    $canvasLabels = $canvasLabels ?? [];

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
                :parent="__('headlines.ideas')"
                :current="$canvasTitle">
                @if ($login::userIsAtLeast($roles::$editor))
                    <li><a href="#/ideas/boardDialog">{!! __('links.icon.create_new_board') !!}</a></li>
                @endif
                <li class="border"></li>
                @foreach ($allCanvas as $canvasRow)
                    <li><a href='{{ BASE_URL }}/ideas/showBoards/{{ $canvasRow['id'] }}'>{{ $tpl->escape($canvasRow['title']) }}</a></li>
                @endforeach
            </x-global::subjectSwitcher>
        @else
            <h1>{!! __('headlines.ideas') !!}</h1>
        @endif
    </div>
    @if (count($allCanvas) > 0)
        <div class="pageheader-right">
            <span class="dropdown dropdownWrapper headerEditDropdown">
                <a href="javascript:void(0)" class="dropdown-toggle btn btn-transparent" data-toggle="dropdown"><i class="fa-solid fa-ellipsis-v"></i></a>
                <ul class="dropdown-menu editCanvasDropdown ">
                    @if ($login::userIsAtLeast($roles::$editor))
                        <li><a href="#/ideas/boardDialog/{{ $currentCanvas }}">{!! __('links.icon.edit') !!}</a></li>
                        <li><a href="{{ BASE_URL }}/ideas/delCanvas/{{ $currentCanvas }}" class="delete">{!! __('links.icon.delete') !!}</a></li>
                    @endif
                </ul>
            </span>
        </div>
    @endif
</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner" id="ideaBoards" style="min-height:350px;">
        {!! $tpl->displayNotification() !!}

        <div class="row">
            <div class="col-md-4">
                @if ($login::userIsAtLeast($roles::$editor))
                    @if (count($allCanvas) > 0)
                        <x-global::forms.button tag="a" link="#/ideas/ideaDialog?type=idea" contentRole="primary" id="customersegment"><span
                                    class="far fa-lightbulb"></span>{!! __('buttons.add_idea') !!}</x-global::forms.button>
                    @endif
                @endif
            </div>

            <div class="col-md-4 center">
            </div>
            <div class="col-md-4">
                <div class="pull-right">
                    <div class="btn-group viewDropDown">
                        <button class="btn dropdown-toggle" data-toggle="dropdown">{!! __('buttons.idea_wall') !!} {!! __('links.view') !!}</button>
                        <ul class="dropdown-menu">
                            <li><a href="{{ BASE_URL }}/ideas/showBoards{{ ! empty($currentCanvas) ? '/'.(int) $currentCanvas : '' }}" class="active">{!! __('buttons.idea_wall') !!}</a></li>
                            <li><a href="{{ BASE_URL }}/ideas/advancedBoards{{ ! empty($currentCanvas) ? '/'.(int) $currentCanvas : '' }}" class="">{!! __('buttons.idea_kanban') !!}</a></li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>

        <div class="clearfix"></div>

        @if (count($allCanvas) > 0)
            <div id="ideaMason" class="sortableTicketList" style="padding-top:10px;">

                @foreach ($canvasItems as $row)
                    <div class="ticketBox" id="item_{{ $row['id'] }}" data-value="{{ $row['id'] }}">

                        <div class="row">
                            <div class="col-md-12">

                                @if ($login::userIsAtLeast($roles::$editor))
                                    <div class="inlineDropDownContainer" style="float:right;">

                                        <a href="javascript:void(0);" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                                            <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                        </a>
                                        &nbsp;&nbsp;&nbsp;
                                        <ul class="dropdown-menu">
                                            <li class="nav-header">{!! __('subtitles.edit') !!}</li>
                                            <li><a href="#/ideas/ideaDialog/{{ $row['id'] }}" class="" data="item_{{ $row['id'] }}"> {!! __('links.edit_canvas_item') !!}</a></li>
                                            <li><a href="#/ideas/delCanvasItem/{{ $row['id'] }}" class="delete" data="item_{{ $row['id'] }}"> {!! __('links.delete_canvas_item') !!}</a></li>

                                        </ul>
                                    </div>
                                @endif

                                <h4><a href="#/ideas/ideaDialog/{{ $row['id'] }}"
                                       data="item_{{ $row['id'] }}">{{ $tpl->escape($row['description']) }}</a></h4>

                                <div class="mainIdeaContent">
                                    <div class="kanbanCardContent">

                                        <div class="kanbanContent" style="margin-bottom: 20px; max-height:none;">
                                            {!! $tpl->escapeMinimal($row['data']) !!}
                                        </div>

                                    </div>
                                </div>

                                <div class="clearfix" style="padding-bottom: 8px;"></div>

                                <x-ideas::chip-status :idea-id="$row['id']" :box="$row['box']" :labels="$canvasLabels" />


                                <x-global::elements.author-avatar class="dropRight" :user-id="$row['author']" :name="trim(($row['authorFirstname'] ?? '').' '.($row['authorLastname'] ?? ''))" />

                                <div class="pull-right" style="margin-right:10px;">

                                    <a href="#/ideas/ideaDialog/{{ $row['id'] }}"
                                       class="" data="item_{{ $row['id'] }}"
                                        {!! $row['commentCount'] == 0 ? 'style="color: grey;"' : '' !!}>
                                        <span class="fas fa-comments"></span></a> <small>{{ $row['commentCount'] }}</small>

                                        @php $ideaTags = array_filter(array_map('trim', explode(',', (string) ($row['tags'] ?? '')))); @endphp
                                        @if (count($ideaTags) > 0)
                                            &nbsp;
                                            <span class="dropdown">
                                                <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">
                                                    <i class="fa fa-tags" aria-hidden="true"></i> <small>{{ count($ideaTags) }}</small>
                                                </a>
                                                <ul class="dropdown-menu pull-right">
                                                    <li style="padding:10px"><div class="tagsinput readonly">
                                                        @foreach ($ideaTags as $tag)
                                                            <span class="tag"><span>{{ $tag }}</span></span>
                                                        @endforeach
                                                    </div></li>
                                                </ul>
                                            </span>
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

                @endforeach

            </div>
            @if (count($canvasItems) == 0)
                <div class='center'>
                    <div style='width:30%' class='svgContainer'>
                        {!! file_get_contents(ROOT . '/dist/images/svg/undraw_new_ideas_jdea.svg') !!}
                    </div>

                    <h3>{!! __('headlines.have_an_idea') !!}</h3><br />
                    {!! __('subtitles.start_collecting_ideas') !!}<br/><br/>
                </div>
            @endif
            <div class="clearfix"></div>

        @else
            <br/><br/>
            <div class='center'>
                <div style='width:30%' class='svgContainer'>
                    {!! file_get_contents(ROOT . '/dist/images/svg/undraw_new_ideas_jdea.svg') !!}
                </div>

                <h3>{!! __('headlines.have_an_idea') !!}</h3><br />
                {!! __('subtitles.start_collecting_ideas') !!}<br/><br/>
                @if ($login::userIsAtLeast($roles::$editor))
                <x-global::forms.button tag="a" link="javascript:void(0)"
                   class="addCanvasLink" contentRole="primary">{!! __('links.icon.create_new_board') !!}</x-global::forms.button>
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
                            <x-global::forms.text-input name="canvastitle" placeholder="{{ __('input.placeholders.name_for_idea_board') }}"
                                   style="width:90%" />
                        </div>
                        <div class="modal-footer">
                            <x-global::forms.button inputType="button" contentRole="tertiary"
                                    data-dismiss="modal">{!! __('buttons.close') !!}</x-global::forms.button>
                            <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.create_board')" name="newCanvas"/>
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
                            <x-global::forms.text-input name="canvastitle" value="{{ $canvasTitle }}"
                                   style="width:90%" />
                        </div>
                        <div class="modal-footer">
                            <x-global::forms.button inputType="button" contentRole="tertiary"
                                    data-dismiss="modal">{!! __('buttons.close') !!}</x-global::forms.button>
                            <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.save')" name="editCanvas"/>
                        </div>
                    </form>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

        <div class="clearfix"></div>

    </div>
</div>

@once
@push('scripts')
<script type="text/javascript">

    jQuery(document).ready(function () {

        leantime.ideasController.initMasonryWall();
        leantime.ideasController.initBoardControlModal();
        leantime.ideasController.initWallImageModals();

        @if ($login::userIsAtLeast($roles::$editor))
        @else
        leantime.authController.makeInputReadonly(".maincontentinner");
        @endif

        @if (isset($_GET['showIdeaModal']))
            @php
                if ($_GET['showIdeaModal'] == '') {
                    $modalUrl = '&type=idea';
                } else {
                    $modalUrl = '/' . (int) $_GET['showIdeaModal'];
                }
            @endphp

        leantime.ideasController.openModalManually("{{ BASE_URL }}/ideas/ideaDialog{{ $modalUrl }}");
        window.history.pushState({}, document.title, '{{ BASE_URL }}/ideas/showBoards{{ ! empty($currentCanvas) ? '/'.(int) $currentCanvas : '' }}');

        @endif
    });

</script>
@endpush
@endonce

@endsection
