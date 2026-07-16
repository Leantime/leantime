@extends($layout)

@section('content')

    @php
        $summary = $report['summaries'][$projectId] ?? null;
    @endphp

    <x-global::pageheader :icon="'fa fa-chart-bar'">
        <div class="row">
            <div class="col-lg-8">
                <h5>{{ session('currentProjectClient') ? session('currentProjectClient') . ' // ' : '' }}{{ session('currentProjectName') }}</h5>
                <h1>{!! __('headlines.status_report') !!}</h1>
            </div>
            <div class="col-lg-4" style="text-align: right;">
                <x-global::forms.button tag="a" link="javascript:window.print();" class="btn-secondary hideOnPrint">
                    <i class="fa fa-print"></i> {{ __('label.print_report') }}
                </x-global::forms.button>
            </div>
        </div>
    </x-global::pageheader>

    <div class="maincontent">
        <div class="maincontentinner">

            {!! $tpl->displayNotification() !!}

            <div class="tw-flex tw-items-center tw-justify-between tw-flex-wrap tw-gap-2 tw-mb-4 hideOnPrint">
                <ul class="tabs-list tw-m-0" style="display:inline-flex; gap: 4px;">
                    <li class="active"><a href="{{ BASE_URL }}/reports/project">{{ __('label.status_report_tab') }}</a></li>
                    <li><a href="{{ BASE_URL }}/reports/show">{{ __('label.delivery_metrics_tab') }}</a></li>
                </ul>

                <x-global::periodpicker
                    :period="$period"
                    :url="BASE_URL.'/reports/project'"
                    :hxUrl="BASE_URL.'/hx/reports/projectReport/get'"
                    target="#reportBody" />
            </div>

            @include('reports::partials.projectReportBody', ['report' => $report, 'period' => $period, 'projectId' => $projectId])

        </div>
    </div>

@endsection
