@extends($layout)

@section('content')

@php
    $projectData = $projectData ?? [];
@endphp

<x-global::layout.page-header icon="fa {{ $tpl->getModulePicture() }}" :eyebrow="session('currentProjectClient').' // '.session('currentProjectName')">
    {!! __('headlines.edit_todo') !!}
    <x-slot:actions>
        <a href="{{ session('lastPage') }}" class="backBtn"><i class="far fa-arrow-alt-circle-left"></i> {!! __('links.go_back') !!}</a>
    </x-slot:actions>
</x-global::layout.page-header>

<div class="maincontent">

    <x-global::layout.content-box>

        {!! $tpl->displayNotification() !!}

        <div class="tabbedwidget tab-primary ticketTabs" style="visibility:hidden;">

            <ul>
                <li><a href="#ticketdetails"><span class="fa fa-star"></span> {!! __('tabs.ticketDetails') !!}</a></li>
                <li><a href="#subtasks"><span class="fa fa-list-check"></span> {!! __('tabs.subtasks') !!} ({{ $numSubTasks }})</a></li>
                <li><a href="#files"><span class="fa fa-file"></span> {!! __('tabs.files') !!} ({{ $numFiles }})</a></li>
                @if (session('userdata.role') != 'client')
                    @module('timesheets', $ticket->projectId)
                        <li><a href="#timesheet" id="timesheetTab"><span class="fa fa-clock"></span> {!! __('tabs.time_tracking') !!}</a></li>
                    @endmodule
                @endif
                <li><a href="#history"><span class="fa fa-clock-rotate-left"></span> {!! __('tabs.history') !!}</a></li>
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
                @module('timesheets', $ticket->projectId)
                    <div id="timesheet">
                        @include('tickets::submodules.timesheet')
                    </div>
                @endmodule
            @endif

            <div id="history">
                <x-global::hx :for="\Leantime\Domain\Tickets\Hxcontrollers\TicketHistory::class" :id="$ticket->id" trigger="intersect once" loaderCount="3" />
            </div>
        </div>

    </x-global::layout.content-box>

    <x-global::layout.content-box>
        <form method="post" action="{{ BASE_URL }}/tickets/showTicket/{{ $ticket->id }}#comments" class="formModal">
            <input type="hidden" name="comment" value="1" />
            @include('comments::submodules.generalComment', ['formUrl' => BASE_URL.'/tickets/showTicket/'.$ticket->id])
        </form>
    </x-global::layout.content-box>

</div>

@once @push('scripts')
<script type="text/javascript">

    jQuery(function () {
        leantime.ticketsController.initTicketTabs();

        jQuery(window).resize();

    });

</script>
@endpush @endonce

@endsection
