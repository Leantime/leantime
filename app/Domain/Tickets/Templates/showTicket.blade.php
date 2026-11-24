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

        <x-global::navigation.section-tabs class="ticketTabs" :label="__('tabs.ticketDetails')" remember="query:tab">
            <x-slot:tabs>
                <x-global::navigation.section-tabs.tab for="ticketdetails" icon="fa-star">{!! __('tabs.ticketDetails') !!}</x-global::navigation.section-tabs.tab>
                <x-global::navigation.section-tabs.tab for="subtasks" icon="fa-list-check">{!! __('tabs.subtasks') !!} ({{ $numSubTasks }})</x-global::navigation.section-tabs.tab>
                <x-global::navigation.section-tabs.tab for="files" icon="fa-file">{!! __('tabs.files') !!} ({{ $numFiles }})</x-global::navigation.section-tabs.tab>
                @if (session('userdata.role') != 'client')
                    @module('timesheets', $ticket->projectId)
                        <x-global::navigation.section-tabs.tab for="timesheet" icon="fa-clock" id="timesheetTab">{!! __('tabs.time_tracking') !!}</x-global::navigation.section-tabs.tab>
                    @endmodule
                @endif
                <x-global::navigation.section-tabs.tab for="history" icon="fa-clock-rotate-left">{!! __('tabs.history') !!}</x-global::navigation.section-tabs.tab>
            </x-slot:tabs>

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
        </x-global::navigation.section-tabs>

    </x-global::layout.content-box>

    <x-global::layout.content-box>
        <form method="post" action="{{ BASE_URL }}/tickets/showTicket/{{ $ticket->id }}#comments" class="formModal">
            <input type="hidden" name="comment" value="1" />
            @include('comments::submodules.generalComment', ['formUrl' => BASE_URL.'/tickets/showTicket/'.$ticket->id, 'commentProjectId' => $ticket->projectId])
        </form>
    </x-global::layout.content-box>

</div>

@once @push('scripts')
<script type="text/javascript">

    jQuery(function () {

        jQuery(window).resize();

    });

</script>
@endpush @endonce

@endsection
