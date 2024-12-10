@php use Leantime\Core\Support\EditorTypeEnum; @endphp


<x-global::content.modal.modal-buttons />

<div class="min-w-[80vw]">
    <h1><?= $tpl->__('headlines.new_to_do') ?></h1>

    @displayNotification()

    <div class="row">

        <div class="col-md-7">
            <form hx-post="{{ BASE_URL }}/tickets/newTicket" hx-trigger="submit" hx-swap="none"
                hx-indicator="#save-indicator">
                {{-- @include("tickets::includes.ticketDetails") --}}
                <input type="hidden" name="saveTicket" value="1">
                <label class="pl-m pb-sm">📄 Details</label>

                <x-global::forms.text-input type="text" name="headline" value="{{ $ticket->headline }}"
                    labelText="Title" variant="title" />

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
                    <x-global::forms.text-editor name="description" customId="ticketDescription" :type="EditorTypeEnum::Complex->value"
                        :value="$ticket->description !== null ? $ticket->description : ''" />
                    <br />
                </div>
                <br>

                <div class="flex items-center gap-2">
                    <x-global::forms.button variant="primary" labelText="Save" />

                    <x-global::forms.button tag="button" type="button" variant="link" contentRole="ghost"
                        labelText="Cancel" onclick="htmx.find('#modal-wrapper #main-page-modal').close();" />

                    <div id="save-indicator" class="htmx-indicator">
                        <span class="loading loading-spinner"></span> Saving...
                    </div>
                </div>
            </form>

        </div>
        <div class="col-md-5" style="border-radius:10px; padding:0px;">
            <x-global::navigations.tabs name="ticket-details" variant="bordered" size="md">
                <x-slot:contents>
                    <x-global::navigations.tabs.content id="ticket-settings" ariaLabel="Settings" classExtra="p-sm"
                        :checked="true">
                        <x-tickets::settings :ticket="$ticket" :allAssignedprojects="$allAssignedprojects" :statusLabels="$statusLabels" :ticketTypes="$ticketTypes"
                            :priorities="$priorities" :efforts="$efforts" :remainingHours="$remainingHours" />
                    </x-global::navigations.tabs.content>
                </x-slot:contents>
            </x-global::navigations.tabs>
        </div>

    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function() {

    });

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
</script>
