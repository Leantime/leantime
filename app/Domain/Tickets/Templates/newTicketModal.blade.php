@php
    $projectData = $projectData ?? [];
    $todoTypeIcons = $ticketTypeIcons ?? [];
@endphp

<div style="min-width:90%">
        <h1>{!! __('headlines.new_to_do') !!}</h1>

        {!! $tpl->displayNotification() !!}

        <div class="tabbedwidget tab-primary ticketTabs" style="visibility:hidden;">

            <ul>
                <li><a href="#ticketdetails">{!! __('tabs.ticketDetails') !!}</a></li>
            </ul>

            <div id="ticketdetails">
                <form class="formModal" action="{{ BASE_URL }}/tickets/newTicket" method="post">
                    @include('tickets::submodules.ticketDetails')
                </form>
            </div>

        </div>
</div>
        <br />


<script type="text/javascript">


    jQuery(document).ready(function(){

        @if (isset($_GET['closeModal']))
        jQuery.nmTop().close();
        @endif

        leantime.ticketsController.initTicketTabs();

        @if ($login::userIsAtLeast($roles::$editor))

            leantime.ticketsController.initDueDateTimePickers();

            leantime.dateController.initDatePicker(".dates");
            leantime.dateController.initDateRangePicker(".editFrom", ".editTo");

            leantime.ticketsController.initTagsInput();

            leantime.ticketsController.initEffortDropdown();
            leantime.ticketsController.initStatusDropdown();

        jQuery(".ticketTabs select").chosen();

        @else
            leantime.authController.makeInputReadonly(".nyroModalCont");

        @endif

        @if ($login::userHasRole([$roles::$commenter]))
            leantime.commentsController.enableCommenterForms();
        @endif

    });

</script>
