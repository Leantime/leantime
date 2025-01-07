@php
    use Leantime\Core\Support\EditorTypeEnum;

    $tags = explode(',', $ticket->tags);

@endphp

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

        <form hx-post="/hx/tickets/showTicket/{{ $ticket->id }}" hx-trigger="submit" hx-swap="none"
            hx-indicator="#save-indicator">
            <input type="hidden" name="saveTicket" value="1">

            <x-global::forms.text-input type="text" name="headline" value="{{ $ticket->headline }}" placeholder="Add Title"
                variant="title" />

            <x-global::forms.select label-text="Tags" name="tags[]" content-role="secondary" variant="tags">
                @foreach ($tags as $tag)
                    <option value="{{ $tag }}" selected>{{ $tag }}</option>
                @endforeach
            </x-global::forms.select>

            <div class="viewDescription mce-content-body input-bordered">
                <div class="min-h-[100px]">
                    <p class="input-bordered">
                        @if (!empty($ticket->description))
                            {!! $ticket->description !!}
                        @else
                            Add Description
                        @endif
                    </p>
                </div>
            </div>

            <div class="form-group" id="descriptionEditor" style="display:none;">
                <x-global::forms.text-editor name="description" customId="ticketDescription" :type="EditorTypeEnum::Complex->value"
                    :value="$ticket->description !== null ? $ticket->description : ''" />
                <br />
            </div>
            <br>

            <div class="flex items-center gap-2">
                <x-global::forms.button variant="primary" labelText="Save" scale="sm" />

                {{-- TODO: This should just close the modal --}}
                <x-global::forms.button tag="button" variant="link" scale="sm" contentRole="ghost"
                    labelText="Cancel" name="cancel" type="button"
                    onclick="htmx.find('#modal-wrapper #main-page-modal').close();" />
                <div id="save-indicator" class="htmx-indicator">
                    <span class="loading loading-spinner"></span> Saving...
                </div>
            </div>
        </form>
    </div>


    <div class="col-md-5" style="border-radius:10px; padding:0px;">
        <x-global::content.tabs name="ticket-details" variant="bordered" size="md" class="mb-2">
            <x-slot:headings>
                <x-global::content.tabs.heading name="connections">Connections</x-global::content.tabs.heading>
                <x-global::content.tabs.heading name="discussion">Discussion</x-global::content.tabs.heading>
                <x-global::content.tabs.heading name="subtask">Subtasks</x-global::content.tabs.heading>
                <x-global::content.tabs.heading name="files">Files</x-global::content.tabs.heading>
                <x-global::content.tabs.heading name="timesheet">Timesheet</x-global::content.tabs.heading>
                <x-global::content.tabs.heading name="ticket-settings">Settings</x-global::content.tabs.heading>
            </x-slot:headings>

            <x-slot:contents>
                <x-global::content.tabs.content name="connections" ariaLabel="Connections" classExtra="p-sm"
                    :checked="true">
                    Connections
                </x-global::content.tabs.content>

                <x-global::content.tabs.content name="discussion" ariaLabel="Discussion" classExtra="p-sm">
                    <x-comments::list :module="'tickets'" :statusUpdates="'false'" :moduleId="$ticket->id" />
                </x-global::content.tabs.content>

                <x-global::content.tabs.content name="subtask" ariaLabel="Subtasks" classExtra="p-sm">
                    <x-tickets::subtasks :ticket="$ticket" />
                </x-global::content.tabs.content>

                <x-global::content.tabs.content name="files" ariaLabel="Files" classExtra="p-sm">
                    <x-tickets::files :ticket="$ticket" />
                </x-global::content.tabs.content>
                <x-global::content.tabs.content name="timesheet" ariaLabel="Timesheet" classExtra="p-sm">
                    <x-tickets::timesheet :ticket="$ticket" :userInfo="$userInfo" :remainingHours="$remainingHours" :timesheetValues="$timesheetValues"
                        :userHours="$userHours" />
                </x-global::content.tabs.content>
                <x-global::content.tabs.content name="ticket-settings" ariaLabel="Settings" classExtra="p-sm">
                    <x-tickets::settings :ticket="$ticket" :allAssignedprojects="$allAssignedprojects" :statusLabels="$statusLabels" :ticketTypes="$ticketTypes"
                        :priorities="$priorities" :efforts="$efforts" :remainingHours="$remainingHours"
                        url="{{ BASE_URL }}/hx/tickets/showTicket/{{ $ticket->id }}" />
                </x-global::content.tabs.content>
            </x-slot:contents>
        </x-global::content.tabs>
    </div>
</div>

<script>
    jQuery(document).ready(function() {

        //leantime.ticketsController.initTagsInput();

        //Set accordion states
        //All accordions start open
        //leantime.editorController.initComplexEditor();
        //tinymce.activeEditor.hide()

        htmx.on('htmx:afterRequest', (event) => {
            if (event.detail.successful && event.target.matches('form')) {
                jQuery('#descriptionEditor').hide();
                jQuery('.viewDescription').show();
            }
        });
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
