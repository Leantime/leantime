@php
    $availableStrategyBoards = $tpl->get('availableStrategyBoards');
    $canvasProgress = $tpl->get('canvasProgress');
@endphp

<div class="pageheader">
    <div class="pageicon"><span class="fa-solid fa-chess"></span></div>
    <div class="pagetitle">
        <h1>{{ $tpl->__('headlines.blueprints') }}</h1>
    </div>
</div>

{!! $tpl->displayNotification() !!}

<div class="maincontent">

    <div class="row">
        <div class="col-md-12">
            <div class="maincontentinner">
                <h5 class="subtitle">Jump right back in</h5>
                <div class="row">
                @foreach ($tpl->get('recentProgressCanvas') as $board)
                    <div class="col-md-3">
                        <div class="profileBox">
                            <div class="commentImage icon">
                                <i class="{{ $board['icon'] }}"></i>
                            </div>
                            <span class="userName">
                                    <small>{{ $tpl->__($board['name']) }} ({{ $board['count'] }})</small><br />

                                    <a href="{{ BASE_URL }}/{{ $board['module'] }}/showCanvas/{{ $board['lastCanvasId'] }}">
                                        {{ $tpl->escape($board['lastTitle']) }}
                                    </a><br />
                                <small>{{ $tpl->__('label.last_updated') }} {{ format($board['lastUpdate'])->date() }} {{ format($board['lastUpdate'])->time() }}</p>
                                </small>
                                </span>
                               <div class="clearall"></div>
                            @php
                                $percentDone = 0;
                                if (isset($canvasProgress[$board['module']])) {
                                    $percentDone = round($canvasProgress[$board['module']] * 100);
                                }
                            @endphp
                            <br />
                            <div class="progress">
                                <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="{{ $percentDone }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $percentDone }}%">
                                    <span class="sr-only">{{ sprintf($tpl->__('text.percent_complete'), $percentDone) }}</span>
                                </div>
                            </div>
                            {{ sprintf($tpl->__('text.percent_complete'), $percentDone) }}


                        </div>
                    </div>
                @endforeach

                @if (! is_array($tpl->get('recentProgressCanvas')) || count($tpl->get('recentProgressCanvas')) == 0)
                    <div class="col-md-12"><br /><br /><div class="center">
                        <div style="width:30%" class="svgContainer">
                            {!! file_get_contents(ROOT . '/dist/images/svg/undraw_design_data_khdb.svg') !!}
                        </div>
                        <h3>{{ $tpl->__('headline.no_blueprints_yet') }}</h3>
                        <br />{{ $tpl->__('text.no_blueprints_yet') }}
                        <br /><a href="{{ BASE_URL }}/valuecanvas/showCanvas" class="btn btn-primary">{{ $tpl->__('button.start_here_project_value') }}</a>
                    </div></div>
                @endif
                </div>

            </div>
        </div>
    </div>

    @if ($login::userIsAtLeast($roles::$editor))
    <div class="row">
        <div class="col-md-12">
            <div class="maincontentinner">
                <h5 class="accordionTitle" id="accordion_link_other">
                    <a href="javascript:void(0)" class="accordion-toggle" id="accordion_toggle_other" onclick="accordionToggle('other');">
                        <i class="fa fa-angle-down"></i> Templates
                    </a>
                </h5>
                <p style="padding-left:19px;">{{ $tpl->__('description.other_tools') }}</p>
                <div id="accordion_other" class="row teamBox" style="padding-left:19px;">

                    @foreach ($tpl->get('otherBoards') as $board)
                        @if (! isset($board['visible']) || $board['visible'] === 1)
                        <div class="col-md-3">
                            <div class="profileBox" style="min-height: 125px;">
                                <div class="commentImage icon">
                                    <i class="{{ $board['icon'] }}"></i>
                                </div>
                                <span class="userName">
                            <a href="{{ BASE_URL }}/{{ $board['module'] }}/showCanvas">
                                {{ $tpl->__($board['name']) }}
                            </a>
                        </span>
                                {{ $tpl->__($board['description']) }}
                                <div class="clearall"></div>


                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>


            </div>
        </div>
    </div>
    @endif

</div>

<script>
    function accordionToggle(id) {

        let currentLink = jQuery("#accordion_toggle_"+id).find("i.fa");

        if(currentLink.hasClass("fa-angle-right")){
            currentLink.removeClass("fa-angle-right");
            currentLink.addClass("fa-angle-down");
            jQuery('#accordion_'+id).slideDown("fast");
        }else{
            currentLink.removeClass("fa-angle-down");
            currentLink.addClass("fa-angle-right");
            jQuery('#accordion_'+id).slideUp("fast");
        }

    }
</script>
