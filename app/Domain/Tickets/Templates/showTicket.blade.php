
<div class="modal-icons">

    @if(isset($ticket->date))
        <x-global::dates.date-info :date="$ticket->date" :type="\Leantime\Core\Support\DateTimeInfoEnum::UpcatedOnAt" /> |
    @endif

    <?php if ($ticket->id != '') {?>
        <a href="#/tickets/delTicket/<?php echo $ticket->id;?>" class="danger" data-tippy-content="Delete"><i class='fa fa-trash-can'></i></a>
        <a href="javascript:void(0);" data-tippy-content="Copy Url" onclick="leantime.snippets.copyToClipboard('<?=BASE_URL ?>/dashboard/show/#/tickets/shotTicket/<?=$ticket->id ?>')"><i class="fa fa-link"></i></a>
    <?php } ?>
</div>

<div style="min-width:1400px"></div>

<div class="tw-float-left tw-pt-[3px] tw-pl-m tw-pr-m">
    <h1>#{{ $ticket->id }}</h1>
</div>
<div class="tw-float-left">
    <x-tickets::type-select :ticket="$ticket" :ticketTypes="$ticketTypes" />
</div>

<div class="tw-float-left">
    <x-global::forms.tags value="{{ $ticket->tags }}" name="tags" autocomplete-tags="true"></x-global::forms.tags>
</div>
<div class="clearall"></div>

<div class="row">
    <div class="col-md-7">

        <div class="row tw-pb-l">
            <div class="col-md-12">
                <input type="text" value="<?php $tpl->e($ticket->headline); ?>" name="headline" class="main-title-input " autocomplete="off" style="width:99%;" placeholder="<?=$tpl->__('input.placeholders.enter_title_of_todo')?>"/>
            </div>
        </div>

        <div class="row">
            <div class="col-md-2">
                <label class="tw-pl-m tw-pt-xs">🚨 {{ __('label.priority') }}</label>
            </div>
            <div class="col-md-5">
                <x-tickets::priority-select :ticket="$ticket" :priorities="$priorities" />
            </div>
        </div>

        <div class="row">
            <div class="col-md-2">
                <label class="tw-pl-m tw-pt-xs">👕  {{ __('label.effort')  }}</label>
            </div>
            <div class="col-md-5">
                <x-tickets::effort-select :ticket="$ticket" :efforts="$efforts" />
            </div>
        </div>

        <div class="row tw-pb-xl">
            <div class="col-md-2">
                <label class="tw-pl-m tw-pt-xs">📅  {{ __('label.dates') }}</label>
            </div>
            <div class="col-md-5">
                <x-global::dates.datepicker no-date-label="{{ __('text.anytime') }}" :value="$ticket->dateToFinish"/>
            </div>
        </div>



        <label class="tw-pl-m tw-pb-sm">📄 Details</label>
        <div class="viewDescription mce-content-body">
            <div class="tw-pl-sm">
                <?php echo $tpl->escapeMinimal($ticket->description); ?>
            </div>
        </div>
        <div class="form-group" id="descriptionEditor" style="display:none;">
            <textarea name="description" id="ticketDescription"
                              class="complexEditor"><?php echo $ticket->description !== null ? htmlentities($ticket->description) : ''; ?></textarea><br/>
        </div>


    </div>
    <div class="col-md-5" style="border-radius:10px; padding:0px;">
        <x-global::content.tabs class="">
            <x-slot:headings class="tw-sticky tw-top-0 !tw-bg-[--secondary-background]">
                <x-global::tabs.heading name="connections">Connections</x-global::tabs.heading>
                <x-global::tabs.heading name="discussion">Discussions</x-global::tabs.heading>
                <x-global::tabs.heading name="subtask">Subtasks</x-global::tabs.heading>
                <x-global::tabs.heading name="files">Files</x-global::tabs.heading>
            </x-slot:headings>

            <x-slot:contents>
                <x-global::tabs.content name="connections" class="tw-p-sm">
                    Connections
                </x-global::tabs.content>

                <x-global::tabs.content name="discussion" class="tw-p-sm">
                    <x-comments::list :module="'ticket'" :statusUpdates="'false'" :moduleId="$ticket->id" />
                </x-global::tabs.content>

                <x-global::tabs.content name="subtask" class="tw-p-sm">
                    <x-tickets::subtasks :ticket="$ticket" />
                </x-global::tabs.content>

                <x-global::tabs.content name="files" class="tw-p-sm">
                    <x-tickets::subtasks :ticket="$ticket" />
                </x-global::tabs.content>

            </x-slot:contents>
        </x-global::content.tabs>
    </div>
</div>

<script>

    jQuery(document).ready(function(){

        leantime.ticketsController.initTagsInput();

        //Set accordion states
        //All accordions start open
        leantime.editorController.initComplexEditor();
        tinymce.activeEditor.hide()
    });

    leantime.editorController.initComplexEditor();

    jQuery(".viewDescription").click(function(e){

        if(!jQuery(e.target).is("a")) {
            e.stopPropagation();
            jQuery(this).hide();
            jQuery('#descriptionEditor').show('fast',
                function() {
                    tinymce.activeEditor.show();
                }
            );
        }
    });

    Prism.highlightAll();

</script>
