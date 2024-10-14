@props([
    'ticket' => false,
    'onTheClock' => false
 ])

@if ($login::userIsAtLeast(\Leantime\Domain\Auth\Models\Roles::$editor))

    <div class="inlineDropDownContainer" style="float:right;">

        @php
        $labelText = '<a href="javascript:void(0);" class="dropdown-toggle ticketDropDown" data-toggle="dropdown"><i class="fa fa-ellipsis-v" aria-hidden="true"></i></a>';
    @endphp
    
    <x-global::content.context-menu 
        :labelText="$labelText"
        class="ticketDropDown"
        align="start"
        contentRole="menu"
    >
        <!-- Menu Header -->
        <li class="nav-header">{{ __("subtitles.todo") }}</li>
    
        <!-- Menu Items -->
        <x-global::actions.dropdown.item 
            href="#/tickets/showTicket/{{ $ticket['id'] }}"
        >
            <i class="fa fa-edit"></i> {{ __("links.edit_todo") }}
        </x-global::actions.dropdown.item>
    
        <x-global::actions.dropdown.item 
            href="#/tickets/moveTicket/{{ $ticket['id'] }}"
        >
            <i class="fa-solid fa-arrow-right-arrow-left"></i> {{ __("links.move_todo") }}
        </x-global::actions.dropdown.item>
    
        <x-global::actions.dropdown.item 
            href="#/tickets/delTicket/{{ $ticket['id'] }}" 
            class="delete"
        >
            <i class="fa fa-trash"></i> {{ __("links.delete_todo") }}
        </x-global::actions.dropdown.item>
    
        <!-- Menu Header -->
        <li class="nav-header border">{{ __("subtitles.track_time") }}</li>
    
        <!-- Include Timer Link -->
        @include('tickets::includes.timerLink', ['parentTicketId' => $ticket['id'], 'onTheClock' => $onTheClock])
    </x-global::content.context-menu>
    
    </div>

@endif
