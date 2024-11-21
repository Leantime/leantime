<x-global::content.modal.modal-buttons>

        @if(isset($ticket->date))
            <x-global::content.date-info class="leading-8" :date="$ticket->date" :type="\Leantime\Core\Support\DateTimeInfoEnum::UpcatedOnAt" /> |
        @endif
        @if ($ticket->id != '')
            <x-global::content.modal.header-button variant="delete" href="#/tickets/delTicket/{{  $ticket->id }}"/>
            <x-global::content.modal.header-button variant="link" href="{{  BASE_URL }}/dashboard/show/#/tickets/showTicket/{{  $ticket->id }}"/>
        @endif

</x-global::content.modal.modal-buttons>

<div style="min-width:1400px"></div>

<div class="float-left pt-[3px] pr-1">
    <h1>#{{ $ticket->id }}</h1>
</div>
<div class="float-left">
    <x-tickets::type-select :ticket="$ticket" :ticketTypes="$ticketTypes" variant="chip"/>
</div>

{{--<div class="float-left">--}}
{{--    <x-global::forms.tags value="{{ $ticket->tags }}" name="tags" autocomplete-tags="true"></x-global::forms.tags>--}}
{{--</div>--}}

<div class="clear"></div>

<div class="row">
    <div class="col-md-7">

        <x-global::forms.text-input
            type="text"
            name="headline"
            value="{!! $tpl->escape($ticket->headline) !!}"
            placeholder="{!! $tpl->__('input.placeholders.enter_title_of_todo') !!}"
            variant="title"
            class="w-[99%]"
            autocomplete="off"
        />

        <x-tickets::priority-select :ticket="$ticket" :priorities="$priorities" label-position="left" />

        <x-tickets::effort-select :ticket="$ticket" :efforts="$efforts" label-position="left" />

        <x-tickets::duedate :date="$ticket->dateToFinish" label-position="left" variant="input"/>

        {{-- <x-global::forms.select
            id="select-test"
            name="select-test"
            labelText="Select an option"
            labelRight="Optional"
            caption="Choose wisely"
            size="lg"
            state="normal"
            variant="multiple"
            validationText="Please select one option"
            validationState="error"
            search="true"
            >
                <x-global::forms.select.select-option value="1">Option 1</x-global::forms.select.select-option>
                <x-global::forms.select.select-option value="2">Option 2</x-global::forms.select.select-option>
                <x-global::forms.select.select-option value="3">Option 3</x-global::forms.select.select-option>
        </x-global::forms.select> --}}

        <label class="pl-m pb-sm">📄 Details</label>
        <div class="viewDescription mce-content-body">
            <div class="pl-sm">
                <?php echo $tpl->escapeMinimal($ticket->description); ?>
            </div>
        </div>
        <div class="form-group" id="descriptionEditor" style="display:none;">
            <textarea name="description" id="ticketDescription"
                              class="complexEditor"><?php echo $ticket->description !== null ? htmlentities($ticket->description) : ''; ?></textarea><br/>
        </div>


    </div>
    {{-- Previous Tabs --}}
    {{-- <div class="col-md-5" style="border-radius:10px; padding:0px;">
        <x-global::content.tabs class="">
            <x-slot:headings class="sticky top-0 !bg-[--secondary-background]">
                <x-global::content.tabs.heading name="connections">Connections</x-global::content.tabs.heading>
                <x-global::content.tabs.heading name="discussion">Discussions</x-global::content.tabs.heading>
                <x-global::content.tabs.heading name="subtask">Subtasks</x-global::content.tabs.heading>
                <x-global::content.tabs.heading name="files">Files</x-global::content.tabs.heading>
            </x-slot:headings>

            <x-slot:contents>
                <x-global::content.tabs.content name="connections" class="p-sm">
                    Connections
                </x-global::content.tabs.content>

                <x-global::content.tabs.content name="discussion" class="p-sm">
                    <x-comments::list :module="'ticket'" :statusUpdates="'false'" :moduleId="$ticket->id" />
                </x-global::content.tabs.content>

                <x-global::content.tabs.content name="subtask" class="p-sm">
                    <x-tickets::subtasks :ticket="$ticket" />
                </x-global::content.tabs.content>

                <x-global::content.tabs.content name="files" class="p-sm">
                    <x-tickets::subtasks :ticket="$ticket" />
                </x-global::content.tabs.content>

            </x-slot:contents>
        </x-global::content.tabs>
    </div>
    -- }}


    {{-- NEW - Calls navigations/tabs component --}}
    <div class="col-md-5" style="border-radius:10px; padding:0px;">
        <x-global::navigations.tabs name="ticket-details" variant="bordered" size="md">
            <x-slot:contents>
                <x-global::navigations.tabs.content id="connections" ariaLabel="Connections" classExtra="p-sm" :checked="true">
                    Connections
                </x-global::navigations.tabs.content>

                <x-global::navigations.tabs.content id="discussion" ariaLabel="Discussion" classExtra="p-sm">
                    <x-comments::list :module="'ticket'" :statusUpdates="'false'" :moduleId="$ticket->id" />
                </x-global::navigations.tabs.content>

                <x-global::navigations.tabs.content id="subtask" ariaLabel="Subtasks" classExtra="p-sm">
                    <x-tickets::subtasks :ticket="$ticket" />
                </x-global::navigations.tabs.content>

                <x-global::navigations.tabs.content id="files" ariaLabel="Files" classExtra="p-sm">
                    <x-tickets::files :ticket="$ticket" />
                </x-global::navigations.tabs.content>
            </x-slot:contents>
        </x-global::navigations.tabs>
    </div>
</div>

<script>

    jQuery(document).ready(function(){

        //leantime.ticketsController.initTagsInput();

        //Set accordion states
        //All accordions start open
        //leantime.editorController.initComplexEditor();
        //tinymce.activeEditor.hide()
    });

    //leantime.editorController.initComplexEditor();

    /*
    jQuery(".viewDescription").click(function(e){

        if(!jQuery(e.target).is("a")) {
            e.stopPropagation();
            jQuery(this).hide();
            jQuery('#descriptionEditor').show('fast',
                function() {
                    //tinymce.activeEditor.show();
                }
            );
        }
    });*/

    Prism.highlightAll();

</script>

