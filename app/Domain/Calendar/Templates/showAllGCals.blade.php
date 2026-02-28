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

@dispatchEvent('beforePageHeaderOpen')
<div class="pageheader">
    @dispatchEvent('afterPageHeaderOpen')
    <form action="{{ BASE_URL }}/index.php?act=tickets.showAll" method="post" class="searchbar">
        <input type="text" name="term" placeholder="To search type and hit enter..." />
    </form>

    <div class="pageicon"><span class="fa {{ $tpl->getModulePicture() }}"></span></div>
    <div class="pagetitle">
        <h5>{{ __('OVERVIEW') }}</h5>
        <h1>{{ __('ALL_GCCALS') }}</h1>
    </div>
    @dispatchEvent('beforePageHeaderClose')
</div>
@dispatchEvent('afterPageHeaderClose')

<div class="maincontent">
    <div class="maincontentinner">
        <form action="">

            @dispatchEvent('afterFormOpen')

            <table cellpadding="0" cellspacing="0" border="0" class="allTickets table table-bordered" id="allTickets">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>{{ __('NAME') }}</th>
                        <th>{{ __('URL') }}</th>
                        <th>{{ __('COLOR') }}</th>
                    </tr>
                </thead>

                <tbody>
                @foreach($tpl->get('allCalendars') as $row)
                    <tr>
                        <td>{!! $tpl->displayLink('calendar.editGCal', $row['id'], ['id' => $row['id']]) !!}</td>
                        <td>{!! $tpl->displayLink('calendar.editGCal', $row['name'], ['id' => $row['id']]) !!}</td>
                        <td>{{ $row['url'] }}</td>
                        <td><span style="background-color: {{ $row['colorClass'] }}; padding: 2px 8px; border-radius: 3px;">{{ $row['colorClass'] }}</span></td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            @dispatchEvent('beforeFormClose')

        </form>
    </div>
</div>
