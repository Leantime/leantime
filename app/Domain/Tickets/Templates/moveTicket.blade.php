@php
    $ticket = $tpl->get('ticket');
@endphp

@if($ticket->type == 'milestone')
    <x-globals::elements.section-title>{{ __('headline.move_milestone') }}</x-globals::elements.section-title>
@else
    <x-globals::elements.section-title>{{ __('headline.move_todo') }}</x-globals::elements.section-title>
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

    <x-globals::forms.select id="projectSelector" name="projectId">
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
    </x-globals::forms.select>

    <div class="tw:mt-8"></div>
    <x-globals::forms.button submit type="primary" name="move">{{ __('buttons.move') }}</x-globals::forms.button>
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
