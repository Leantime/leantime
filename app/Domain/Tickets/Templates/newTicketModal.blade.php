@php
    $projectData = $projectData ?? [];
    $todoTypeIcons = $ticketTypeIcons ?? [];
@endphp

<div style="min-width:90%">
        <x-global::actions.modal-header icon="fa fa-plus">
            <x-slot:title>{!! __('headlines.new_to_do') !!}</x-slot:title>
        </x-global::actions.modal-header>

        {!! $tpl->displayNotification() !!}

        <x-global::navigation.section-tabs class="ticketTabs" :label="__('tabs.ticketDetails')" remember="query:tab">
            <x-slot:tabs>
                <x-global::navigation.section-tabs.tab for="ticketdetails" icon="fa-star">{!! __('tabs.ticketDetails') !!}</x-global::navigation.section-tabs.tab>
            </x-slot:tabs>

            <div id="ticketdetails">
                <form class="formModal" action="{{ BASE_URL }}/tickets/newTicket" method="post">
                    @include('tickets::submodules.ticketDetails')
                </form>
            </div>

        </x-global::navigation.section-tabs>
</div>
        <br />


<script type="text/javascript">


    jQuery(document).ready(function(){

        @if (isset($_GET['closeModal']))
        jQuery.nmTop().close();
        @endif


        @can('tickets.create')

            leantime.ticketsController.initDueDateTimePickers();

            leantime.dateController.initDatePicker(".dates");
            leantime.dateController.initDateRangePicker(".editFrom", ".editTo");




        @else
            leantime.authController.makeInputReadonly(".nyroModalCont");
            @can('comments.create')
                leantime.commentsController.enableCommenterForms();
            @endcan
        @endcan

    });

</script>
