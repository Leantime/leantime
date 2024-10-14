@extends($layout)
@section('content')

    @php

        use Leantime\Domain\Comments\Repositories\Comments;
        use Leantime\Domain\Goalcanvas\Services\Goalcanvas;

        $canvasSvc = app()->make(Goalcanvas::class);


        $elementName = 'goal';
        /**
         * showCanvasTop.inc template - Top part of the main canvas page
         *
         * Required variables:
         * - goal      Name of current canvas
         */

        $canvasTitle = '';

        //get canvas title
        foreach ($allCanvas as $canvasRow) {
            if ($canvasRow['id'] == $currentCanvas) {
                $canvasTitle = $canvasRow['title'];
                $canvasId = $canvasRow['id'];
                break;
            }
        }

    @endphp

    <style>
        .canvas-row {
            margin-left: 0px;
            margin-right: 0px;
        }

        .canvas-title-only {
            border-radius: var(--box-radius-small);
        }

        h4.canvas-element-title-empty {
            background: white !important;
            border-color: white !important;
        }

        div.canvas-element-center-middle {
            text-align: center;
        }
    </style>

    <div class="pageheader">
        <div class="pageicon"><span class='fa {{ $canvasIcon }}'></span></div>
        <div class="pagetitle">
            <h5>{{ session('currentProjectClient') . ' // ' . session('currentProjectName') }}</h5>

            <h1>{{ __('headline.goal.dashboardboard') }} //
                @include('goalcanvas::partials.goalBoard')
            </h1>
        </div>
    </div>

    <div class="maincontent">

        <div class="row" style="margin-bottom:20px; ">
            <div class="col-md-4">
                <div class="bigNumberBox" style="padding: 29px 15px;">
                    <h2>Progress: {{ round($goalStats['avgPercentComplete']) }}%</h2>

                    <div class="progress" style="margin-top:5px;">
                        <div class="progress-bar progress-bar-success" role="progressbar"
                            aria-valuenow="{{ round($goalStats['avgPercentComplete']) }}" aria-valuemin="0"
                            aria-valuemax="100" style="width: {{ $goalStats['avgPercentComplete'] }}%">
                            <span
                                class="sr-only">{{ sprintf(__('text.percent_complete'), round($goalStats['avgPercentComplete'])) }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2"></div>
            <div class="col-md-2">
                <div class="bigNumberBox priority-border-4">
                    <h2>On Track</h2>
                    <span class="content">{{ $goalStats['goalsOnTrack'] }}</span>
                </div>
            </div>
            <div class="col-md-2">
                <div class="bigNumberBox priority-border-3">
                    <h2>At Risk</h2>
                    <span class="content">{{ $goalStats['goalsAtRisk'] }}</span>
                </div>
            </div>
            <div class="col-md-2">
                <div class="bigNumberBox priority-border-1">
                    <h2>Miss</h2>
                    <span class="content">{{ $goalStats['goalsMiss'] }}</span>
                </div>
            </div>
        </div>

        <div class="maincontentinner">
            <div class="row">
                <div class="col-md-6"></div>
            </div>
            @if (count($allCanvas) > 0)
                @foreach ($allCanvas as $canvasRow)
                    {{-- @php                    
                        $canvasItems = $canvasSvc->getCanvasItemsById($canvasRow['id']);
                    @endphp --}}

                    <x-goalcanvas::canvas
                        id="{{ $canvasRow['id'] }}"
                        canvasTitle="{{ $canvasRow['title'] }}"
                        {{-- :goalItems="$canvasItems" --}}
                        :statusLabels="$statusLabels"
                        :relatesLabels="$relatesLabels"
                        :users="$users"
                    />
            
                @endforeach
            @endif
        </div>



{{--
 * showCanvasBottom.blade.php template - Bottom part of the main canvas page
 *
 * Required variables:
 * - goal      Name of current canvas
--}}

        @if (count($allCanvas) > 0)
            {{--  --}}
        @else
            <br><br>
            <div class='center'>
                <div class='svgContainer'>
                    {!! file_get_contents(ROOT . '/dist/images/svg/undraw_design_data_khdb.svg') !!}
                </div>
                <h3>{{ __('headlines.goal.analysis') }}</h3>
                <br>{!! __('text.goal.helper_content') !!}

                @if ($login::userIsAtLeast($roles::$editor))
                    <br><br>
                    <x-global::forms.button
                        tag="a"
                        href="javascript:void(0)"
                        class="addCanvasLink btn btn-primary"
                    >
                        {!! __('links.icon.create_new_board') !!}
                    </x-global::forms.button>
                @endif
            </div>
        @endif

        @if (!empty($disclaimer) && count($allCanvas) > 0)
            <small class="align-center">{{ $disclaimer }}</small>
        @endif

        {{-- {!! $tpl->viewFactory->make($tpl->getTemplatePath('canvas', 'modals'), $__data)->render() !!} --}}

    </div>
    </div>

    <script type="text/javascript">
        jQuery(document).ready(function() {
            if (jQuery('#searchCanvas').length > 0) {
                new SlimSelect({
                    select: '#searchCanvas'
                });
            }

            leantime.goalCanvasController.setRowHeights();
            leantime.canvasController.setCanvasName('goal');
            leantime.canvasController.initFilterBar();

            @if ($login::userIsAtLeast($roles::$editor))
                leantime.canvasController.initUserDropdown('goalcanvas');
                leantime.canvasController.initStatusDropdown('goalcanvas');
                leantime.canvasController.initRelatesDropdown('goalcanvas');
            @else
                leantime.authController.makeInputReadonly(".maincontentinner");
            @endif

});
</script>

@endsection
