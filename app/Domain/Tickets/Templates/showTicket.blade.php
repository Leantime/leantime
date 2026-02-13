@php
    $ticket = $tpl->get('ticket');
    $projectData = $tpl->get('projectData');
@endphp

<div class="pageheader">

    <div class="pull-right padding-top">
        <a href="{{ session('lastPage') }}" class="backBtn"><i class="far fa-arrow-alt-circle-left"></i> {{ __('links.go_back') }}</a>
    </div>

    <div class="pageicon"><span class="fa {{ $tpl->getModulePicture() }}"></span></div>
    <div class="pagetitle">
        <h5>{{ e(session('currentProjectClient') . ' // ' . session('currentProjectName')) }}</h5>
        <h1>{{ __('headlines.edit_todo') }}</h1>
    </div>

</div><!--pageheader-->

<div class="maincontent">

    <div class="maincontentinner">

        {!! $tpl->displayNotification() !!}

        <div class="tabbedwidget tab-primary ticketTabs" style="visibility:hidden;">

            <ul>
                <li><a href="#ticketdetails">{{ __('tabs.ticketDetails') }}</a></li>
                <li><a href="#subtasks">{{ __('tabs.subtasks') }} ({{ $tpl->get('numSubTasks') }})</a></li>
                <li><a href="#files">{{ __('tabs.files') }} ({{ $tpl->get('numFiles') }})</a></li>
                @if(session('userdata.role') != 'client')
                    <li><a href="#timesheet" id="timesheetTab">{{ __('tabs.time_tracking') }}</a></li>
                @endif
            </ul>

            <div id="ticketdetails">
                <form class="formModal" action="{{ BASE_URL }}/tickets/showTicket/{{ $ticket->id }}" method="post">
                    @php $tpl->displaySubmodule('tickets-ticketDetails') @endphp
                </form>
            </div>

            <div id="subtasks">
                @php $tpl->displaySubmodule('tickets-subTasks') @endphp
            </div>

            <div id="files">
                <form action='#files' method='POST' enctype="multipart/form-data" class="formModal">
                    @php $tpl->displaySubmodule('tickets-attachments') @endphp
                </form>
            </div>

            @if(session('userdata.role') != 'client')
                <div id="timesheet">
                    @php $tpl->displaySubmodule('tickets-timesheet') @endphp
                </div>
            @endif
        </div>

    </div>

    <div class="maincontentinner">
        <form method="post" action="{{ BASE_URL }}/tickets/showTicket/{{ $ticket->id }}#comments" class="formModal">
            <input type="hidden" name="comment" value="1" />
            @php
                $tpl->assign('formUrl', BASE_URL . '/tickets/showTicket/' . $ticket->id);
                $tpl->displaySubmodule('comments-generalComment');
            @endphp
        </form>
    </div>

</div>

<script type="text/javascript">

    jQuery(window).load(function () {
        leantime.ticketsController.initTicketTabs();

        jQuery(window).resize();

    });

</script>
