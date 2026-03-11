@php
    $ticket = $tpl->get('ticket');
@endphp

<x-globals::layout.page-header :icon="$tpl->getModulePicture()" headline="{{ __('headlines.new_to_do') }}" subtitle="{{ e(session('currentProjectClient') . ' // ' . session('currentProjectName')) }}">
    <x-slot:actions>
        <a href="{{ session('lastPage') }}" class="backBtn"><x-globals::elements.icon name="arrow_circle_left" /> {{ __('links.go_back') }}</a>
    </x-slot:actions>
</x-globals::layout.page-header>

<div class="maincontent">
    <div class="maincontentinner">

        {!! $tpl->displayNotification() !!}

        <div class="lt-tabs tabbedwidget ticketTabs" data-tabs>

            <ul role="tablist">
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

    jQuery(window).load(function () {
        jQuery(window).resize();
    });

</script>
