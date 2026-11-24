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

        <x-global::navigation.section-tabs class="ticketTabs" :label="__('tabs.ticketDetails')" remember="query:tab">
            <x-slot:tabs>
                <x-global::navigation.section-tabs.tab for="ticketdetails" icon="fa-star">{!! __('tabs.ticketDetails') !!}</x-global::navigation.section-tabs.tab>
            </x-slot:tabs>

            <div id="ticketdetails">
                <form class="ticketModal" action="{{ BASE_URL }}/tickets/newTicket" method="post">
                    @include('tickets::submodules.ticketDetails')
                </form>
            </div>

        </x-global::navigation.section-tabs>
    </x-global::layout.content-box>
</div>

@once @push('scripts')
<script type="text/javascript">

    jQuery(document).ready(function() {
        jQuery(window).resize();
    });

</script>
@endpush @endonce

@endsection
