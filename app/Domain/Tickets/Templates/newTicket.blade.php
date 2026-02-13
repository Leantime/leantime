@php
    $ticket = $tpl->get('ticket');
@endphp

<div class="pageheader">

    <div class="pull-right padding-top">
        <a href="{{ session('lastPage') }}" class="backBtn"><i class="far fa-arrow-alt-circle-left"></i> {{ __('links.go_back') }}</a>
    </div>

    <div class="pageicon"><span class="fa {{ $tpl->getModulePicture() }}"></span></div>
    <div class="pagetitle">
        <h5>{{ e(session('currentProjectClient') . ' // ' . session('currentProjectName')) }}</h5>
        <h1>{{ __('headlines.new_to_do') }}</h1>
    </div>
</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">

        {!! $tpl->displayNotification() !!}

        <div class="tabbedwidget tab-primary ticketTabs">

            <ul>
                <li>
                    <a href="#ticketdetails">{{ __('tabs.ticketDetails') }}</a>
                </li>
            </ul>

            <div id="ticketdetails">
                <form class="ticketModal" action="{{ BASE_URL }}/tickets/newTicket" method="post">
                    @php $tpl->displaySubmodule('tickets-ticketDetails') @endphp
                </form>
            </div>

        </div>
    </div>
</div>

<script type="text/javascript">

    jQuery(document).ready(function() {
        leantime.ticketsController.initTicketTabs();
        leantime.ticketsController.initTagsInput();
    });


    jQuery(window).load(function () {
        jQuery(window).resize();
    });

</script>
