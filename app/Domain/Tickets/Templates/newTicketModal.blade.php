@php
    $ticket = $tpl->get('ticket');
    $projectData = $tpl->get('projectData');
    $todoTypeIcons = $tpl->get('ticketTypeIcons');
@endphp

<div style="min-width:90%">
    <h1>{{ __('headlines.new_to_do') }}</h1>

    {!! $tpl->displayNotification() !!}

    <div class="lt-tabs tabbedwidget ticketTabs" style="visibility:hidden;" data-tabs>
        <ul role="tablist">
            <li><a href="#ticketdetails">{{ __('tabs.ticketDetails') }}</a></li>
        </ul>

        <div id="ticketdetails">
            <form class="formModal" action="{{ BASE_URL }}/tickets/newTicket" method="post">
                @php $tpl->displaySubmodule('tickets-ticketDetails') @endphp
            </form>
        </div>
    </div>
</div>
<br />

<script type="text/javascript">
    jQuery(document).ready(function(){

        @if(isset($_GET['closeModal']))
            leantime.modals.closeModal();
        @endif

        @if($login::userIsAtLeast($roles::$editor))

            leantime.ticketsController.initDueDateTimePickers();

            leantime.dateController.initDatePicker(".dates");
            leantime.dateController.initDateRangePicker(".editFrom", ".editTo");

            leantime.ticketsController.initTagsInput();

            leantime.ticketsController.initEffortDropdown();
            leantime.ticketsController.initStatusDropdown();

            jQuery(".ticketTabs select").chosen();

        @else
            leantime.authController.makeInputReadonly("#global-modal-content");
        @endif

        @if($login::userHasRole([$roles::$commenter]))
            leantime.commentsController.enableCommenterForms();
        @endif

    });
</script>
