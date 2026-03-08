<script type="text/javascript">

    @dispatchEvent('scripts.afterOpen')

    $(document).ready(function() {
        $("#allTickets").tablesorter({
            sortList: [[0,0]],
            widgets: ['zebra']
        }).tablesorterPager({container: $("#pager")});

        $("#allTickets").bind("sortStart", function() {
            $('#loader').show();
        }).bind("sortEnd", function() {
            $('#loader').hide();
        });
    });

    @dispatchEvent('scripts.beforeClose')

</script>
<link rel="stylesheet" type="text/css" href="includes/libs/fullCalendar/fullcalendar.css" />

<x-globals::layout.page-header :icon="$tpl->getModulePicture()" headline="{{ __('ALL_GCCALS') }}" subtitle="{{ __('OVERVIEW') }}">
    <x-slot:actions>
        <form action="{{ BASE_URL }}/index.php?act=tickets.showAll" method="post" class="searchbar">
            <x-globals::forms.text-input name="term" placeholder="To search type and hit enter..." />
        </form>
    </x-slot:actions>
</x-globals::layout.page-header>

<div class="maincontent">
    <div class="maincontentinner">
        <form action="">

            @dispatchEvent('afterFormOpen')

            <x-globals::elements.table>
                <x-slot:head>
                    <tr>
                        <th>Id</th>
                        <th>{{ __('NAME') }}</th>
                        <th>{{ __('URL') }}</th>
                        <th>{{ __('COLOR') }}</th>
                    </tr>
                </x-slot:head>

                @foreach($tpl->get('allCalendars') as $row)
                    <tr>
                        <td>{!! $tpl->displayLink('calendar.editGCal', $row['id'], ['id' => $row['id']]) !!}</td>
                        <td>{!! $tpl->displayLink('calendar.editGCal', $row['name'], ['id' => $row['id']]) !!}</td>
                        <td>{{ $row['url'] }}</td>
                        <td><span style="background-color: {{ $row['colorClass'] }}; color: #fff; padding: 2px 8px; border-radius: 3px;">{{ $row['colorClass'] }}</span></td>
                    </tr>
                @endforeach
            </x-globals::elements.table>

            @dispatchEvent('beforeFormClose')

        </form>
    </div>
</div>
