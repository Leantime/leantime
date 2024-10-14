<?php
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$currentArticle = $tpl->get('article');
$wikiHeadlines = $tpl->get("wikiHeadlines");
?>


<?php

if (!isset($_GET['closeModal'])) {
    echo $tpl->displayNotification();
}

$id = "";
if (isset($currentArticle->id)) {
    $id = $currentArticle->id;
}

?>

<form class="formModal" method="post" action="<?=CURRENT_URL ?>">

    <div class="row">
        <div class="col-md-2">
            <div class="row-fluid marginBottom">
                <h4 class="widgettitle title-light">
                    <span class="fa fa-folder"></span><?php echo $tpl->__('subtitles.organization'); ?>
                </h4>
                <label>Parent</label>
                <select name="parent" style="width:100%;">
                    <option value="0">None</option>
                    <?php foreach ($wikiHeadlines as $parent) {?>
                        <?php if ($id != $parent->id) {?>
                            <option value="<?=$parent->id ?>"
                                    <?=($parent->id == $currentArticle->parent) ? "selected='selected'" : '' ?> ><?php $tpl->e($parent->title) ?></option>
                        <?php } ?>
                    <?php } ?>
                </select>

                <label><?=$tpl->__('label.status') ?></label>
                <select name="status" style="width:100%;">
                    <option value="draft" <?=$currentArticle->status == 'draft' ? "selected='selected'" : "" ?>><?=$tpl->__('label.draft') ?></option>
                    <option value="published" <?=$currentArticle->status == 'published' ? "selected='selected'" : "" ?>><?=$tpl->__('label.published') ?></option>
                </select>
            </div>

            <?php if ($id !== '') { ?>
                <h4 class="widgettitle title-light"><span class="fa fa-link"></span> <?=$tpl->__("headlines.linked_milestone") ?> <i class="fa fa-question-circle-o helperTooltip" data-tippy-content="<?=$tpl->__("tooltip.link_milestones_tooltip") ?>"></i></h4>

                <ul class="sortableTicketList" style="width:99%">
                    <?php
                    if ($currentArticle->milestoneId == '') {
                        ?>
                        <li class="ui-state-default center" id="milestone_0">
                            <h4><?=$tpl->__("headlines.no_milestone_link") ?></h4>
                            <?=$tpl->__("text.use_milestone_to_track_leancanvas") ?><br />
                            <div class="row" id="milestoneSelectors">
                                <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
                                    <div class="col-md-12">
                                        <a href="javascript:void(0);" onclick="leantime.leanCanvasController.toggleMilestoneSelectors('new');"><?=$tpl->__("links.create_link_milestone") ?></a>
                                        | <a href="javascript:void(0);" onclick="leantime.leanCanvasController.toggleMilestoneSelectors('existing');"><?=$tpl->__("links.link_existing_milestone") ?></a>

                                    </div>
                                <?php } ?>
                            </div>
                            <div class="row" id="newMilestone" style="display:none;">
                                <div class="col-md-12">
                                    <textarea name="newMilestone"></textarea><br />
                                    <input type="hidden" name="type" value="milestone" />
                                    <input type="hidden" name="leancanvasitemid" value="<?php echo $id; ?> " />
                                    <input type="button" value="<?=$tpl->__("buttons.save") ?>" onclick="jQuery('#primaryArticleSubmitButton').click()" class="btn btn-primary" />
                                    <a href="javascript:void(0);" onclick="leantime.leanCanvasController.toggleMilestoneSelectors('hide');" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> <?=$tpl->__("links.cancel") ?>
                                    </a>
                                </div>
                            </div>

                            <div class="row" id="existingMilestone" style="display:none;">
                                <div class="col-md-12">
                                    <select data-placeholder="<?=$tpl->__("input.placeholders.filter_by_milestone") ?>" name="existingMilestone"  class="user-select">
                                        <option value=""><?=$tpl->__("label.all_milestones") ?></option>
                                        <?php foreach ($tpl->get('milestones') as $milestoneRow) {
                                            ?>

                                            <?php echo"<option value='" . $milestoneRow->id . "'";

                                            if (isset($searchCriteria['milestone']) && ($searchCriteria['milestone'] == $milestoneRow->id)) {
                                                echo" selected='selected' ";
                                            }

                                            echo">" . $milestoneRow->headline . "</option>"; ?>
                                            <?php
                                        }     ?>
                                    </select>
                                    <input type="hidden" name="type" value="milestone" />
                                    <input type="hidden" name="articleId" value="<?php echo $id; ?> " />
                                    <input type="button" value="Save" onclick="jQuery('#primaryArticleSubmitButton').click()" class="btn btn-primary" />
                                    <a href="javascript:void(0);"  onclick="leantime.leanCanvasController.toggleMilestoneSelectors('hide');">
                                        <i class="fas fa-times"></i> <?=$tpl->__("links.cancel") ?>
                                    </a>
                                </div>
                            </div>

                        </li>
                        <?php
                    } else {

                        ?>

                        <li class="ui-state-default" id="milestone_<?php echo $currentArticle->milestoneId; ?>" class="leanCanvasMilestone" >

                            <div hx-trigger="load"
                                 hx-indicator=".htmx-indicator"
                                 hx-get="<?=BASE_URL ?>/hx/tickets/milestones/showCard?milestoneId=<?=$currentArticle->milestoneId ?>">
                                <div class="htmx-indicator">
                                    <?=$tpl->__("label.loading_milestone") ?>
                                </div>
                            </div>
                            <a href="<?=CURRENT_URL ?>?removeMilestone=<?=$currentArticle->milestoneId ?>" class="formModal"><i class="fa fa-close"></i> <?=$tpl->__("links.remove") ?></a>

                        </li>
                    <?php } ?>

                </ul>

            <?php } ?>

            <br />

        </div>
        <div class="col-md-8">


            <div class="btn-group inlineDropDownContainerLeft">
                <button data-selected="graduation-cap" type="button"
                        class="icp icp-dd btn btn-default dropdown-toggle iconpicker-container titleIconPicker"
                        data-toggle="dropdown">
                    <span class="iconPlaceholder">
                        <i class="fa fa-file"></i>
                    </span>
                    <span class="caret"></span>
                </button>
                <div class="dropdown-menu"></div>
            </div>
            <input type="hidden" class="articleIcon" value="<?=$currentArticle->data ?>" name="articleIcon"/>

            <input type="text" name="title" class="main-title-input" value="<?=$tpl->escape($currentArticle->title) ?>" placeholder="<?=$tpl->__('input.placeholders.wiki_title') ?>" style="width:80%"/>

            <br />
            <input type="text" value="<?php $tpl->e($currentArticle->tags); ?>" name="tags" id="tags" />

            <textarea class="complexEditor" rows="20" cols="80" id="wikiArticleContentEditor"  name="description"><?=htmlentities($currentArticle->description ?? '') ?></textarea>


                <div class="row">
                    <div class="col-md-10 padding-top-sm">
                        <br />
                        <input type="hidden" name="saveTicket" value="1" />
                        <input type="hidden" id="saveAndCloseButton" name="saveAndCloseArticle" value="0" />
                        <input type="submit" name="saveArticle" value="<?php echo $tpl->__('buttons.save'); ?>" id="primaryArticleSubmitButton"/>
                        <input type="submit" class="btn btn-outline" name="saveAndCloseArticle" onclick="jQuery('#saveAndCloseButton').val('1');" value="<?php echo $tpl->__('buttons.save_and_close'); ?>"/>

                    </div>
                    <div class="col-md-2 align-right padding-top-sm">
                        <?php if (isset($currentArticle->id) && $currentArticle->id != '' && $login::userIsAtLeast($roles::$editor)) { ?>
                            <br />
                            <a href="#/wiki/delArticle/<?php echo $currentArticle->id; ?>" class="delete"><i class="fa fa-trash"></i> <?=$tpl->__('links.delete_article') ?></a>
                        <?php } ?>
                    </div>
                </div>




        </div>
        <div class="col-md-2"></div>
    </div>




</form>

<script>

    jQuery(document).ready(function(){

        leantime.editorController.initComplexEditor();

        <?php if (isset($_GET['closeModal'])) { ?>
            jQuery.nmTop().close();
        <?php } ?>




        jQuery('.iconpicker-container').iconpicker({
            //title: 'Dropdown with picker',
            component:'.btn > .iconPlaceholder',
            input:'.articleIcon',
            inputSearch: true,
            defaultValue:"far fa-file-alt",
            selected: "<?=$currentArticle->data ?>",
            showFooter: false,
            searchInFooter: false,
            icons: [
                {title: "far fa-file-alt", searchTerms:['icons']},
                {title: "fab fa-accessible-icon", searchTerms:['icons']},
                {title: "far fa-address-book", searchTerms:['icons']},
                {title: "fas fa-archive", searchTerms:['icons']},
                {title: "fas fa-asterisk", searchTerms:['icons']},
                {title: "fas fa-balance-scale", searchTerms:['icons']},
                {title: "fas fa-ban", searchTerms:['icons']},
                {title: "fas fa-bell", searchTerms:['icons']},
                {title: "fas fa-binoculars", searchTerms:['icons']},
                {title: "fas fa-birthday-cake", searchTerms:['icons']},
                {title: "fas fa-bolt", searchTerms:['icons']},
                {title: "fas fa-book", searchTerms:['icons']},
                {title: "fas fa-bookmark", searchTerms:['icons']},
                {title: "fas fa-briefcase", searchTerms:['icons']},
                {title: "fas fa-bug", searchTerms:['icons']},
                {title: "far fa-building", searchTerms:['icons']},
                {title: "fas fa-bullhorn", searchTerms:['icons']},
                {title: "far fa-calendar-alt", searchTerms:['icons']},
                {title: "fas fa-chart-bar", searchTerms:['icons']},
                {title: "fas fa-check-circle", searchTerms:['icons']},
                {title: "fas fa-chart-line", searchTerms:['icons']},
                {title: "fas fa-chess", searchTerms:['icons']},
                {title: "fas fa-cogs", searchTerms:['icons']},
                {title: "fas fa-comments", searchTerms:['icons']},
                {title: "fas fa-compass", searchTerms:['icons']},
                {title: "fas fa-database", searchTerms:['icons']},
                {title: "fas fa-envelope", searchTerms:['icons']},
                {title: "fas fa-exclamation-triangle", searchTerms:['icons']},
                {title: "fas fa-flask", searchTerms:['icons']},
                {title: "fas fa-globe", searchTerms:['icons']},
                {title: "fas fa-gem", searchTerms:['icons']},
                {title: "fas fa-graduation-cap", searchTerms:['icons']},
                {title: "fas fa-hand-spock", searchTerms:['icons']},
                {title: "fas fa-heart", searchTerms:['icons']},
                {title: "fas fa-home", searchTerms:['icons']},
                {title: "fas fa-image", searchTerms:['icons']},
                {title: "fas fa-info-circle", searchTerms:['icons']},
                {title: "fas fa-key", searchTerms:['icons']},
                {title: "fas fa-leaf", searchTerms:['icons']},
                {title: "fas fa-life-ring", searchTerms:['icons']},
                {title: "fas fa-lightbulb", searchTerms:['icons']},
                {title: "fas fa-link", searchTerms:['icons']},
                {title: "fas fa-location-arrow", searchTerms:['icons']},
                {title: "fas fa-lock", searchTerms:['icons']},
                {title: "fas fa-map", searchTerms:['icons']},
                {title: "fas fa-map-signs", searchTerms:['icons']},
                {title: "fas fa-money-bill-alt", searchTerms:['icons']},
                {title: "fas fa-paper-plane", searchTerms:['icons']},
                {title: "fas fa-paperclip", searchTerms:['icons']},
                {title: "fas fa-question-circle", searchTerms:['icons']},
                {title: "fas fa-quote-left", searchTerms:['icons']},
                {title: "fas fa-road", searchTerms:['icons']},
                {title: "fas fa-rocket", searchTerms:['icons']},
                {title: "fas fa-shopping-cart", searchTerms:['icons']},
                {title: "fas fa-sitemap", searchTerms:['icons']},
                {title: "fas fa-sliders-h", searchTerms:['icons']},
                {title: "fas fa-star", searchTerms:['icons']},
                {title: "fas fa-tachometer-alt", searchTerms:['icons']},
                {title: "fas fa-thermometer-half", searchTerms:['icons']},
                {title: "fas fa-thumbs-down", searchTerms:['icons']},
                {title: "fas fa-thumbs-up", searchTerms:['icons']},
                {title: "fas fa-trash-alt", searchTerms:['icons']},
                {title: "fas fa-trophy", searchTerms:['icons']},
                {title: "fas fa-user-circle", searchTerms:['icons']},
                {title: "fas fa-utensils", searchTerms:['icons']}
            ]

        });
        jQuery('.iconpicker-container').on('iconpickerSelected', function(event){
           jQuery(".articleIcon").val(event.iconpickerValue);
        });

        leantime.ticketsController.initTagsInput();


    });

</script>


<?php /*


 */
