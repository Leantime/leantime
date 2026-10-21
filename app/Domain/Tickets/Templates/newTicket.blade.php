@extends($layout)

@section('content')

<x-global::layout.page-header icon="fa {{ $tpl->getModulePicture() }}" :eyebrow="session('currentProjectClient').' // '.session('currentProjectName')">
    {!! __('headlines.new_to_do') !!}
    <x-slot:actions>
        <a href="{{ session('lastPage') }}" class="backBtn"><i class="far fa-arrow-alt-circle-left"></i> {!! __('links.go_back') !!}</a>
    </x-slot:actions>
</x-global::layout.page-header>

<div class="maincontent">
    <x-global::layout.content-box>

        {!! $tpl->displayNotification() !!}

        <div class="tabbedwidget tab-primary ticketTabs">

            <ul>
                <li>
                    <a href="#ticketdetails"><span class="fa fa-star"></span> {!! __('tabs.ticketDetails') !!}</a>
                </li>
            </ul>

            <div id="ticketdetails">
                <form class="ticketModal" action="{{ BASE_URL }}/tickets/newTicket" method="post">
                    @include('tickets::submodules.ticketDetails')
                </form>
            </div>

        </div>
    </x-global::layout.content-box>
</div>

@once @push('scripts')
<script type="text/javascript">

    jQuery(document).ready(function() {
        leantime.ticketsController.initTicketTabs();
        jQuery(window).resize();
    });

</script>
@endpush @endonce

@endsection
