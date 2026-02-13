@php
    $ticket = $tpl->get('ticket');
@endphp

@if($ticket->type == 'milestone')
    <h4 class="widgettitle title-light">{{ __('headline.move_milestone') }}</h4>
@else
    <h4 class="widgettitle title-light">{{ __('headline.move_todo') }}</h4>
@endif

<form method="post" action="{{ BASE_URL }}/tickets/moveTicket/{{ $ticket->id }}" class="formModal">
    <h3>#{{ $ticket->id }} - {{ e($ticket->headline) }}</h3> <br />
    <p>
        @if($ticket->type == 'milestone')
            {{ __('text.moving_milestones') }}
        @else
            {{ __('text.moving') }}
        @endif
        <br /><br />
    </p>

    <select id="projectSelector" name="projectId">
        @php
            $lastClient = '';
            $i = 0;
        @endphp
        @foreach($tpl->get('projects') as $projectRow)
            @if($lastClient != $projectRow['clientName'])
                @php $lastClient = $projectRow['clientName']; @endphp
                @if($i > 1)
                    </optgroup>
                @endif
                <optgroup label="{{ e($projectRow['clientName']) }}">
            @endif
            <option value="{{ $projectRow['id'] }}">{{ e($projectRow['name']) }}</option>
            @php $i++; @endphp
        @endforeach
    </select><br /><br /><br /><br />
    <br />
    <input type="submit" value="{{ __('buttons.move') }}" name="move" class="btn btn-primary tw:btn tw:btn-primary" />
    <a class="pull-right" href="javascript:void(0);" onclick="leantime.modals.closeModal();">{{ __('buttons.back') }}</a>
    <div class="clearall"></div>
    <br />
</form>

<script>
    @if(isset($_GET['closeModal']))
        leantime.modals.closeModal();
    @endif

    jQuery(document).ready(function(){
        jQuery("#projectSelector").chosen();
    });
</script>
