@extends($layout)

@section('content')

@php
    $projectData = $tpl->get('projectData');
@endphp

<div class="pageheader">

    <div class="pull-right padding-top">
        <a href="{{ session('lastPage') }}" class="backBtn"><i class="far fa-arrow-alt-circle-left"></i> {!! __('links.go_back') !!}</a>
    </div>

    <div class="pageicon"><span class="fa {{ $tpl->getModulePicture() }}"></span></div>
    <div class="pagetitle">
        <h5>{{ session('currentProjectClient').' // '.session('currentProjectName') }}</h5>
        <h1>{!! __('headlines.edit_todo') !!}</h1>
    </div>

</div><!--pageheader-->

<div class="maincontent">

    <div class="maincontentinner">

        {!! $tpl->displayNotification() !!}

        <div class="tabbedwidget tab-primary ticketTabs" style="visibility:hidden;">

            <ul>
                <li><a href="#ticketdetails">{!! __('tabs.ticketDetails') !!}</a></li>
                <li><a href="#subtasks">{!! __('tabs.subtasks') !!} ({{ $tpl->get('numSubTasks') }})</a></li>
                <li><a href="#files">{!! __('tabs.files') !!} ({{ $tpl->get('numFiles') }})</a></li>
                @if (session('userdata.role') != 'client')
                    <li><a href="#timesheet" id="timesheetTab">{!! __('tabs.time_tracking') !!}</a></li>
                @endif
            </ul>

            <div id="ticketdetails">
                <form class="formModal" action="{{ BASE_URL }}/tickets/showTicket/{{ $ticket->id }}" method="post">
                    @include('tickets::submodules.ticketDetails')
                </form>
            </div>

            <div id="subtasks">

                    @include('tickets::submodules.subTasks')

            </div>

            <div id="files">
                <form action='#files' method='POST' enctype="multipart/form-data" class="formModal">
                    @include('tickets::submodules.attachments')
                </form>
            </div>


            @if (session('userdata.role') != 'client')
                <div id="timesheet">
                    @include('tickets::submodules.timesheet')
                </div>
            @endif
        </div>

    </div>

    <div class="maincontentinner">
        <form method="post" action="{{ BASE_URL }}/tickets/showTicket/{{ $ticket->id }}#comments" class="formModal">
            <input type="hidden" name="comment" value="1" />
            @php
                $tpl->assign('formUrl', BASE_URL.'/tickets/showTicket/'.$ticket->id.'');
            @endphp
            @include('comments::submodules.generalComment')
        </form>
    </div>

</div>

@once @push('scripts')
<script type="text/javascript">

    jQuery(window).load(function () {
        leantime.ticketsController.initTicketTabs();

        jQuery(window).resize();

    });

</script>
@endpush @endonce

@endsection
