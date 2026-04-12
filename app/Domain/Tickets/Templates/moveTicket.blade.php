@if ($ticket->type == 'milestone')
    <h4 class="widgettitle title-light">{!! __('headline.move_milestone') !!} </h4>
@else
    <h4 class="widgettitle title-light">{!! __('headline.move_todo') !!} </h4>
@endif


    <form method="post" action="{{ BASE_URL }}/tickets/moveTicket/{{ $ticket->id }}" class="formModal">
        <h3>#{{ $ticket->id }} - {{ $ticket->headline }}</h3> <br />
        <p>
            @if ($ticket->type == 'milestone')
                {!! __('text.moving_milestones') !!}
            @else
                {!! __('text.moving') !!}
            @endif

            <br /><br />
        </p>

        <select id="projectSelector" name="projectId">
        @php
        $i = 0;
        $lastClient = '';
        foreach ($projects as $projectRow) {
            if ($lastClient != $projectRow['clientName']) {
                $lastClient = $projectRow['clientName'];
                if ($i > 1) {
                    echo '</optgroup>';
                }
                echo "<optgroup label='".$tpl->escape($projectRow['clientName'])."'> ";
            }
            echo "<option value='".$projectRow['id']."'>".$tpl->escape($projectRow['name']).'</option>';
            $i++;
        }
        @endphp
        </select><br /><br /><br /><br />
        <br />
        <input type="submit" value="{{ __('buttons.move') }}" name="move" class="button" />
        <a class="pull-right" href="javascript:void(0);" onclick="jQuery.nmTop().close();">{!! __('buttons.back') !!}</a>
        <div class="clearall"></div>
        <br />
    </form>


<script>
    @if (isset($_GET['closeModal']))
        jQuery.nmTop().close();
    @endif

    jQuery(document).ready(function(){
        jQuery("#projectSelector").chosen();
    });
</script>
