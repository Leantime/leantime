<x-global::actions.modal-header>
    <x-slot:title>{!! $ticket->type == 'milestone' ? __('headline.move_milestone') : __('headline.move_todo') !!}</x-slot:title>
</x-global::actions.modal-header>


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

        <x-global::forms.select enhanced id="projectSelector" name="projectId" aria-label="{{ __('label.project') }}">
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
        </x-global::forms.select><br /><br /><br /><br />
        <br />
        <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.move')" name="move" />
        <x-global::forms.button tag="a" class="pull-right" link="javascript:void(0);" onclick="jQuery.nmTop().close();" contentRole="tertiary">{!! __('buttons.back') !!}</x-global::forms.button>
        <div class="clearall"></div>
        <br />
    </form>


<script>
    @if (isset($_GET['closeModal']))
        jQuery.nmTop().close();
    @endif

</script>
