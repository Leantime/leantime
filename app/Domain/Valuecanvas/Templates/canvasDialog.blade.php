<?php

/**
 * Dialog
 */

foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$canvasName = 'value';


/**
 * canvasDialog.inc template - Generic template for comments
 *
 * Required variables:
 * - $canvasName   Name of current canvas
 */

defined('RESTRICTED') or die('Restricted access');

$canvasItem = $tpl->get('canvasItem');
$canvasTypes = $tpl->get('canvasTypes');
$hiddenStatusLabels = $tpl->get('statusLabels');
$statusLabels = $statusLabels ?? $hiddenStatusLabels;
$hiddenRelatesLabels = $tpl->get('relatesLabels');
$relatesLabels = $relatesLabels ?? $hiddenRelatesLabels;
$dataLabels = $tpl->get('dataLabels');

$id = "";
if (isset($canvasItem['id']) && $canvasItem['id'] != '') {
    $id = $canvasItem['id'];
}
?>

<script type="text/javascript">
    window.onload = function() {
        if (!window.jQuery) {
            //It's not a modal
            location.href="{{ BASE_URL }}/{{ $canvasName }}canvas/showCanvas?showModal=<?php echo $canvasItem['id']; ?>";
        }
    }
</script>

<div class="" style="width:900px;">

  <h4 class="widgettitle title-light" style="padding-bottom: 0"><i class="fas <?=$canvasTypes[$canvasItem['box']]['icon']; ?>"></i> <?=$canvasTypes[$canvasItem['box']]['title']; ?></h4>
  <hr style="margin-top: 5px; margin-bottom: 15px;">
    @displayNotification()

    <x-global::content.modal.form action="{{ BASE_URL }}/{{ $canvasName }}canvas/editCanvasItem/{{ $id }}">

        <input type="hidden" value="<?php echo $tpl->get('currentCanvas'); ?>" name="canvasId" />
        <input type="hidden" value="<?php $tpl->e($canvasItem['box']) ?>" name="box" id="box"/>
        <input type="hidden" value="<?php echo $id ?>" name="itemId" id="itemId"/>

        <x-global::forms.text-input 
            type="text" 
            name="description" 
            value="{!! $tpl->escape($canvasItem['description']) !!}" 
            labelText="{!! $tpl->__('label.description') !!}" 
            class="w-full" 
        />
        <br />
    
        @if (!empty($statusLabels))
            <x-global::forms.select name="status" id="statusCanvas" class="w-1/2" :labelText="__('label.status')">
            </x-global::forms.select>
            <br /><br />
        @else
            <input type="hidden" name="status" value="{{ $canvasItem['status'] ?? array_key_first($hiddenStatusLabels) }}" />
        @endif
        
        @if (!empty($relatesLabels))
            <x-global::forms.select name="relates" id="relatesCanvas" class="w-1/2" :labelText="__('label.relates')">
            </x-global::forms.select>
            <br />
        @endif
    
        <?php } else { ?>
            <input type="hidden" name="relates" value="<?php echo $canvasItem['relates'] ?? array_key_first(
                $hiddenRelatesLabels
            ) ?>" />
        <?php } ?>

        @foreach ([1, 2, 3] as $index)
            @if ($dataLabels[$index]['active'])
                <label>
                    {!! $tpl->__($dataLabels[$index]['title'] . "." . $tpl->escape($canvasItem['box'])) !!}
                </label>
        
                @if (isset($dataLabels[$index]['type']) && $dataLabels[$index]['type'] == 'int')
                    <x-global::forms.text-input 
                        type="number" 
                        name="{!! $dataLabels[$index]['field'] !!}" 
                        value="{!! $canvasItem[$dataLabels[$index]['field']] !!}" 
                    />
                    <br />
        
                @elseif (isset($dataLabels[$index]['type']) && $dataLabels[$index]['type'] == 'string')
                    <x-global::forms.text-input 
                        type="text" 
                        name="{!! $dataLabels[$index]['field'] !!}" 
                        value="{!! $canvasItem[$dataLabels[$index]['field']] !!}" 
                        class="w-full" 
                    />
                    <br />
        
                @else
                    <textarea 
                        name="{!! $dataLabels[$index]['field'] !!}" 
                        rows="3" 
                        cols="10" 
                        class="modalTextArea tinymceSimple w-full"
                    >{!! $canvasItem[$dataLabels[$index]['field']] !!}</textarea>
                    <br />
                @endif
            @else
                <input type="hidden" name="{!! $dataLabels[$index]['field'] !!}" value="" />
            @endif
        @endforeach
    

        <input type="hidden" name="milestoneId" value="<?php echo $canvasItem['milestoneId'] ?>" />
        <input type="hidden" name="changeItem" value="1" />

        <?php if ($id != '') {?>
            <a href="{{ BASE_URL }}/{{ $canvasName }}canvas/delCanvasItem/{{ $id }}" class="{{ $canvasName }}CanvasModal delete right"><i class='fa fa-trash-can'></i> {{ __("links.delete") }}</a>
        <?php } ?>

        <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
            <x-global::forms.button type="submit" id="primaryCanvasSubmitButton">
                {{ __('buttons.save') }}
            </x-global::forms.button>
            
            <x-global::forms.button type="submit" value="closeModal" id="saveAndClose" onclick="leantime.{{ $canvasName }}CanvasController.setCloseModal();">
                {{ __('buttons.save_and_close') }}
            </x-global::forms.button>
            

        <?php } ?>

        <?php if ($id !== '') { ?>
            <br /><br />
            <h4 class="widgettitle title-light"><span class="fa fa-link"></span> <?=$tpl->__("headlines.linked_milestone") ?> <i class="fa fa-question-circle-o helperTooltip" data-tippy-content="<?=$tpl->__("tooltip.link_milestones_tooltip") ?>"></i></h4>

            <ul class="sortableTicketList" style="width: 100%">

            <?php if ($canvasItem['milestoneId'] == '') {?>
                <li class="ui-state-default center" id="milestone_0">
                    <h4><?=$tpl->__("headlines.no_milestone_link") ?></h4>
                        <div class="row" id="milestoneSelectors">
                            <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
                            <div class="col-md-12">
                                <a href="javascript:void(0);" onclick="leantime.canvasController.toggleMilestoneSelectors('new');"><?=$tpl->__("links.create_link_milestone") ?></a>
                                <?php if (count($tpl->get('milestones')) > 0) { ?>
                                    | <a href="javascript:void(0);" onclick="leantime.canvasController.toggleMilestoneSelectors('existing');"><?=$tpl->__("links.link_existing_milestone") ?></a>
                                <?php } ?>
                             </div>
                            <?php } ?>
                        </div>
                        <div class="row" id="newMilestone" style="display:none;">
                            <div class="col-md-12">
                                <x-global::forms.text-input 
                                    type="text" 
                                    name="newMilestone" 
                                    class="w-[50%]" 
                                />
                                <br />
                                <input type="hidden" name="type" value="milestone" />
                                <input type="hidden" name="{{ $canvasName }}canvasitemid" value="<?php echo $id; ?> " />
                                <x-global::forms.button type="button" onclick="jQuery('#primaryCanvasSubmitButton').click()">
                                    {!! __('buttons.save') !!}
                                </x-global::forms.button>
                                
                                <x-global::forms.button type="button" onclick="leantime.canvasController.toggleMilestoneSelectors('hide')">
                                    {!! __('buttons.cancel') !!}
                                </x-global::forms.button>                            </div>
                        </div>

                        <div class="row" id="existingMilestone" style="display:none;">
                            <div class="col-md-12">
                                <x-global::forms.select 
                                    name="existingMilestone" 
                                    class="user-select" 
                                    :labelText="__('input.placeholders.filter_by_milestone')"
                                    data-placeholder="{{ __('input.placeholders.filter_by_milestone') }}"
                                >
                                    <x-global::forms.select.select-option value=""></x-global::forms.select.select-option>
                                
                                    @foreach ($tpl->get('milestones') as $milestoneRow)
                                        <x-global::forms.select.select-option 
                                            :value="$milestoneRow->id" 
                                            :selected="isset($searchCriteria['milestone']) && $searchCriteria['milestone'] == $milestoneRow->id"
                                        >
                                            {!! $milestoneRow->headline !!}
                                        </x-global::forms.select.select-option>
                                    @endforeach
                                </x-global::forms.select>
                            
                                <input type="hidden" name="type" value="milestone" />
                                <input type="hidden" name="{{ $canvasName }}canvasitemid" value="<?php echo $id; ?> " />
                                <x-global::forms.button type="button" onclick="jQuery('#primaryCanvasSubmitButton').click()">
                                    {!! $tpl->__("buttons.save") !!}
                                </x-global::forms.button>
                                
                                <x-global::forms.button type="button" onclick="leantime.canvasController.toggleMilestoneSelectors('hide')">
                                    {!! $tpl->__("buttons.cancel") !!}
                                </x-global::forms.button>                            </div>
                        </div>

                </li>
                <?php
            } else {


                ?>

                    <li class="ui-state-default" id="milestone_<?php echo $canvasItem['milestoneId']; ?>" class="{{ $canvasName }}CanvasMilestone" >
                        <div hx-trigger="load"
                             hx-indicator=".htmx-indicator"
                             hx-get="{{ BASE_URL }}/hx/tickets/milestones/showCard?milestoneId=<?=$canvasItem['milestoneId'] ?>">
                            <div class="htmx-indicator">
                                <?=$tpl->__("label.loading_milestone") ?>
                            </div>
                        </div>
                        <a href="<?=CURRENT_URL ?>?removeMilestone=<?php echo $canvasItem['milestoneId'];?>" class="{{ $canvasName }}CanvasModal delete formModal"><i class="fa fa-close"></i> <?=$tpl->__("links.remove") ?></a>

                    </li>
            <?php } ?>

        </ul>

        <?php } ?>

    </x-global::content.modal.form>

    <?php if ($id !== '') { ?>
        <br />
        <input type="hidden" name="comment" value="1" />
        <h4 class="widgettitle title-light"><span class="fa fa-comments"></span>{{ __("subtitles.discussion") }}</h4>
        @include("comments::includes.generalComment", ["formUrl" => BASE_URL . "/". $canvasName ."canvas/editCanvasItem/" . $id ])

    <?php } ?>
</div>

<script type="text/javascript">
    jQuery(document).ready(function(){

        <?php if (!empty($statusLabels)) { ?>
            new SlimSelect({
                select: '#statusCanvas',
                showSearch: false,
                valuesUseText: false,
                data: [
                    <?php foreach ($statusLabels as $key => $data) { ?>
                        <?php if ($data['active']) { ?>
                            { innerHTML: '<?php echo "<i class=\"fas fa-fw " . $data["icon"] . "\"></i>&nbsp;" . $data['title']; ?>',
                              text: "<?=$data['title'] ?>", value: "<?=$key ?>", selected: <?php echo $canvasItem['status'] == $key ? 'true' : 'false'; ?>},
                        <?php } ?>
                    <?php } ?>
                ]
            });
        <?php } ?>

        <?php if (!empty($relatesLabels)) { ?>
            new SlimSelect({
                select: '#relatesCanvas',
                showSearch: false,
                valuesUseText: false,
                data: [
                    <?php foreach ($relatesLabels as $key => $data) { ?>
                        <?php if ($data['active']) { ?>
                            { innerHTML: '<?php echo "<i class=\"fas fa-fw " . $data["icon"] . "\"></i>&nbsp;" . $data['title']; ?>',
                              text: "<?=$data['title'] ?>", value: "<?=$key ?>", selected: <?php echo $canvasItem['relates'] == $key ? 'true' : 'false'; ?>},
                        <?php } ?>
                    <?php } ?>
                ]
            });
        <?php } ?>

        leantime.editorController.initSimpleEditor();

        <?php if (!$login::userIsAtLeast($roles::$editor)) { ?>
            leantime.authController.makeInputReadonly(".nyroModalCont");

        <?php } ?>

        <?php if ($login::userHasRole([$roles::$commenter])) { ?>
            leantime.commentsController.enableCommenterForms();
        <?php }?>

    })
</script>

