@php use Leantime\Core\Support\EditorTypeEnum; @endphp

<x-global::content.modal.modal-buttons>

    @if (isset($ticket->date))
        <x-global::content.date-info class="leading-8" :date="$ticket->date" :type="\Leantime\Core\Support\DateTimeInfoEnum::UpcatedOnAt" /> |
    @endif
    @if ($ticket->id != '')
        <x-global::content.modal.header-button variant="delete" href="#/tickets/delTicket/{{ $ticket->id }}" />
        <x-global::content.modal.header-button variant="link"
            href="{{ BASE_URL }}/dashboard/show/#/tickets/showTicket/{{ $ticket->id }}" />
    @endif

</x-global::content.modal.modal-buttons>

<div style="min-width:1400px"></div>

<div class="float-left pt-[3px] pr-1">
    <h1>#{{ $ticket->id }}</h1>
</div>
<div class="float-left">
    <x-tickets::type-select :ticket="$ticket" :ticketTypes="$ticketTypes" variant="chip" />
</div>

{{-- <div class="float-left"> --}}
{{--    <x-global::forms.tags value="{{ $ticket->tags }}" name="tags" autocomplete-tags="true"></x-global::forms.tags> --}}
{{-- </div> --}}

<div class="clear"></div>

<div class="row">
    <div class="col-md-7">

        <form hx-post="{{ BASE_URL }}/tickets/showTicket/{{ $ticket->id }}"
              hx-trigger="submit"
              hx-swap="none"
              hx-indicator="#save-indicator">
            <input type="hidden" name="saveTicket" value="1">
            <label class="pl-m pb-sm">📄 Details</label>

            <x-global::forms.text-input 
                type="text" 
                name="headline" 
                value="{{ $ticket->headline }}"
                labelText="Title" 
                variant="title" />

            <div class="viewDescription mce-content-body">
                <div class="min-h-[100px]">
                    @if (!empty($ticket->description))
                        {!! $ticket->description !!}
                    @else
                        <p>Add Description</p>
                    @endif
                </div>
            </div>

            <div class="form-group" id="descriptionEditor" style="display:none;">
                <x-global::forms.text-editor 
                    name="description" 
                    customId="ticketDescription" 
                    :type="EditorTypeEnum::Complex->value"
                    :value="$ticket->description !== null ? $ticket->description : ''" />
                <br />
            </div>
            <br>

            <div class="flex items-center gap-2">
                <x-global::forms.button 
                    variant="primary" 
                    labelText="Save" />
                    

                {{-- TODO: This should just close the modal --}}
                <x-global::forms.button 
                    tag="a"
                    variant="link" 
                    contentRole="ghost" 
                    labelText="Cancel" 
                    href="{{ BASE_URL }}/dashboard/show/#/tickets/showKanban" />
                    
                <div id="save-indicator" class="htmx-indicator">
                    <span class="loading loading-spinner"></span> Saving...
                </div>
            </div>
        </form>
    </div>


    {{-- NEW - Calls navigations/tabs component --}}
    <div class="col-md-5" style="border-radius:10px; padding:0px;">
        <x-global::navigations.tabs name="ticket-details" variant="bordered" size="md">
            <x-slot:contents>
                <x-global::navigations.tabs.content id="connections" ariaLabel="Connections" classExtra="p-sm"
                    :checked="true">
                    Connections
                </x-global::navigations.tabs.content>

                <x-global::navigations.tabs.content id="discussion" ariaLabel="Discussion" classExtra="p-sm">
                    <x-comments::list :module="'tickets'" :statusUpdates="'false'" :moduleId="$ticket->id" />
                </x-global::navigations.tabs.content>

                <x-global::navigations.tabs.content id="subtask" ariaLabel="Subtasks" classExtra="p-sm">
                    <x-tickets::subtasks :ticket="$ticket" />
                </x-global::navigations.tabs.content>

                <x-global::navigations.tabs.content id="files" ariaLabel="Files" classExtra="p-sm">
                    <x-tickets::files :ticket="$ticket" />
                </x-global::navigations.tabs.content>
                <x-global::navigations.tabs.content id="timesheet" ariaLabel="Timesheet" classExtra="p-sm">
                    <x-tickets::timesheet :ticket="$ticket" :userInfo="$userInfo" :remainingHours="$remainingHours" :timesheetValues="$timesheetValues" :userHours="$userHours" />
                </x-global::navigations.tabs.content>
                <x-global::navigations.tabs.content id="ticket-settings" ariaLabel="Settings" classExtra="p-sm">
                    <x-tickets::settings :ticket="$ticket" :allAssignedprojects="$allAssignedprojects" :statusLabels="$statusLabels" :ticketTypes="$ticketTypes"
                        :priorities="$priorities" :efforts="$efforts" :remainingHours="$remainingHours" />
                </x-global::navigations.tabs.content>
            </x-slot:contents>
        </x-global::navigations.tabs>
    </div>
</div>

<script>
    jQuery(document).ready(function() {

        //leantime.ticketsController.initTagsInput();

        //Set accordion states
        //All accordions start open
        //leantime.editorController.initComplexEditor();
        //tinymce.activeEditor.hide()
    });

    //leantime.editorController.initComplexEditor();

    jQuery(".viewDescription").click(function(e) {

        if (!jQuery(e.target).is("a")) {
            e.stopPropagation();
            jQuery(this).hide();
            jQuery('#descriptionEditor').show('fast',
                function() {
                    //tinymce.activeEditor.show();
                }
            );
        }
    });

    Prism.highlightAll();
</script>

