@extends($layout)

@section('content')

<x-global::layout.page-header icon="fa {{ $tpl->getModulePicture() }}" :eyebrow="__('label.overview')">
    {!! __('label.connected_calendars') !!}
    <x-slot:actions>
        <form action="{{ BASE_URL }}/index.php?act=tickets.showAll" method="post" class="searchbar">
            <x-global::forms.text-input name="term" :placeholder="__('input.placeholders.search_type_hit_enter')" />
        </form>
    </x-slot:actions>
</x-global::layout.page-header>

<div class="maincontent">
    <x-global::layout.content-box>
        <form action="">

            @dispatchEvent('afterFormOpen')

            <table cellpadding="0" cellspacing="0" border="0" class="allTickets table table-bordered"
                id="allTickets">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>{!! __('label.name') !!}</th>
                        <th>{!! __('label.calendar_url') !!}</th>
                        <th>{!! __('label.color') !!}</th>
                    </tr>
                </thead>

                <tbody>

                @foreach ($allCalendars as $row)
                    <tr>
                        <td>{!! $tpl->displayLink('calendar.editGCal', $row['id'], ['id' => $row['id']]) !!}</td>
                        <td>{!! $tpl->displayLink('calendar.editGCal', $row['name'], ['id' => $row['id']]) !!}</td>
                        <td>{{ $row['url'] }}</td>
                        <td><span style="color: {{ $row['colorClass'] }}; padding:2px;">{{ $row['colorClass'] }}</span></td>
                    </tr>
                @endforeach

                </tbody>
            </table>

            @dispatchEvent('beforeFormClose')

        </form>
    </x-global::layout.content-box>
</div>

@once
@push('scripts')
<script type="text/javascript">

    @dispatchEvent('scripts.afterOpen')

    $(document).ready(function()
        {
            $("#allTickets").tablesorter({
                sortList:[[0,0]],
                widgets: ['zebra']
            }).tablesorterPager({container: $("#pager")});

            //assign the sortStart event
            $("#allTickets").bind("sortStart",function() {

                $('#loader').show();


            }).bind("sortEnd",function() {

                $('#loader').hide();

           });


        }
    );

    @dispatchEvent('scripts.beforeClose')

</script>
@endpush
@endonce

<link rel='stylesheet' type='text/css' href='includes/libs/fullCalendar/fullcalendar.css' />

@endsection
