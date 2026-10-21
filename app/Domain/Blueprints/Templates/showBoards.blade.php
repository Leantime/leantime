@extends($layout)

@section('content')

<x-global::layout.page-header icon="fa-solid fa-chess">
    {!! __('headlines.blueprints') !!}
</x-global::layout.page-header>

{!! $tpl->displayNotification() !!}

<div class="maincontent">

    <div class="row">
        <div class="col-md-12">
            <x-global::layout.content-box>
                <x-global::elements.section-header as="h5">{{ __('headlines.jump_right_back_in') }}</x-global::elements.section-header>
                <div class="row">
                @foreach ($recentProgressCanvas as $canvasType => $board)
                    <div class="col-md-3">
                        <div class="profileBox">
                            <div class="commentImage icon">
                                <i class="{{ $board['icon'] }}"></i>
                            </div>
                            <span class="userName">
                                    <small>{!! __($board['name']) !!} ({{ $board['count'] }})</small><br />

                                    <a href="{{ BASE_URL }}/{{ $board['module'] }}/showCanvas/{{ $board['lastCanvasId'] }}">
                                        {{ $tpl->escape($board['lastTitle']) }}
                                    </a><br />
                                <small>{!! __('label.last_updated') !!} {{ format($board['lastUpdate'])->date() }} {{ format($board['lastUpdate'])->time() }}</p>
                                </small>
                                </span>
                               <div class="clearall"></div>
                            @php
                                $percentDone = 0;
                                if (isset($canvasProgress[$canvasType])) {
                                    $percentDone = round($canvasProgress[$canvasType] * 100);
                                }
                            @endphp
                            <br />
                            <x-global::feedback.progress :value="$percentDone" />
                            {!! sprintf(__('text.percent_complete'), $percentDone) !!}


                        </div>
                    </div>
                @endforeach

                @if (! is_array($recentProgressCanvas) || count($recentProgressCanvas) == 0)
                    <div class='col-md-12'><br /><br /><div class='center'>
                        <div style='width:30%' class='svgContainer'>
                            {!! file_get_contents(ROOT . '/dist/images/svg/undraw_design_data_khdb.svg') !!}
                        </div>
                        <h3>{!! __('headline.no_blueprints_yet') !!}</h3>
                        <br />{!! __('text.no_blueprints_yet') !!}
                        <br /><x-global::forms.button tag="a" link="{{ BASE_URL }}/blueprints/value/showCanvas" contentRole="primary">{!! __('button.start_here_project_value') !!}</x-global::forms.button>
                    </div></div>
                @endif
                </div>

            </x-global::layout.content-box>
        </div>
    </div>

    @if ($login::userIsAtLeast($roles::$editor))
    <div class="row">
        <div class="col-md-12">
            <x-global::layout.content-box>
                <x-global::elements.accordion id="blueprints-templates" :title="__('label.templates')" open>
                <p>{!! __('description.other_tools') !!}</p>
                <div class="row teamBox">

                    @foreach ($otherBoards as $board)
                        @if (! isset($board['visible']) || $board['visible'] === 1)
                        <div class="col-md-3">
                            <div class="profileBox" style="min-height: 125px;">
                                <div class="commentImage icon">
                                    <i class="{{ $board['icon'] }}"></i>
                                </div>
                                <span class="userName">
                            <a href="{{ BASE_URL }}/{{ $board['module'] }}/showCanvas">
                                {!! __($board['name']) !!}
                            </a>
                        </span>
                                {!! __($board['description']) !!}
                                <div class="clearall"></div>


                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
                </x-global::elements.accordion>

            </x-global::layout.content-box>
        </div>
    </div>
    @endif

</div>


@endsection
