@php
    use Leantime\Core\Support\EditorTypeEnum;
@endphp

<x-global::content.modal.modal-buttons />

<?php
$currentArticle = $tpl->get('article');
$wikiHeadlines = $tpl->get('wikiHeadlines');
?>

@displayNotification()

<?php

$id = '';
if (isset($currentArticle->id)) {
    $id = $currentArticle->id;
}

?>

<x-global::content.modal.form action="{{ CURRENT_URL }}">

    <div class="row">
        <div class="col-md-2">
            <div class="row-fluid marginBottom">
                <h4 class="widgettitle title-light">
                    <span class="fa fa-folder"></span>{{ __('subtitles.organization') }}
                </h4>
                <x-global::forms.select name="parent" :labelText="'Parent'">
                    <x-global::forms.select.select-option value="0">None</x-global::forms.select.select-option>

                    @foreach ($wikiHeadlines as $parent)
                        @if ($id != $parent->id)
                            <x-global::forms.select.select-option :value="$parent->id" :selected="$parent->id == $currentArticle->parent">
                                {!! $tpl->escape($parent->title) !!}
                            </x-global::forms.select.select-option>
                        @endif
                    @endforeach
                </x-global::forms.select>

                <x-global::forms.select name="status" :labelText="__('label.status')">
                    <x-global::forms.select.select-option value="draft" :selected="$currentArticle->status == 'draft'">
                        {!! __('label.draft') !!}
                    </x-global::forms.select.select-option>

                    <x-global::forms.select.select-option value="published" :selected="$currentArticle->status == 'published'">
                        {!! __('label.published') !!}
                    </x-global::forms.select.select-option>
                </x-global::forms.select>

            </div>

            <?php if ($id !== '') { ?>
            <h4 class="widgettitle title-light"><span class="fa fa-link"></span>
                <?= $tpl->__('headlines.linked_milestone') ?> <i class="fa fa-question-circle-o helperTooltip"
                    data-tippy-content="<?= $tpl->__('tooltip.link_milestones_tooltip') ?>"></i></h4>

            <ul class="sortableTicketList" style="width:99%">
                <?php
                    if ($currentArticle->milestoneId == '') {
                        ?>
                <li class="ui-state-default center" id="milestone_0">
                    <h4><?= $tpl->__('headlines.no_milestone_link') ?></h4>
                    <?= $tpl->__('text.use_milestone_to_track_leancanvas') ?><br />
                    <div class="row" id="milestoneSelectors">
                        <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
                        <div class="col-md-12">
                            <a href="javascript:void(0);"
                                onclick="canvasController.toggleMilestoneSelectors('new');"><?= $tpl->__('links.create_link_milestone') ?></a>
                            | <a href="javascript:void(0);"
                                onclick="canvasController.toggleMilestoneSelectors('existing');"><?= $tpl->__('links.link_existing_milestone') ?></a>

                        </div>
                        <?php } ?>
                    </div>
                    <div class="row" id="newMilestone" style="display:none;">
                        <div class="col-md-12">
                            <textarea name="newMilestone"></textarea><br />
                            <input type="hidden" name="type" value="milestone" />
                            <input type="hidden" name="leancanvasitemid" value="<?php echo $id; ?> " />
                            <x-global::forms.button type="button"
                                onclick="jQuery('#primaryArticleSubmitButton').click()" class="btn btn-primary">
                                {{ __('buttons.save') }}
                            </x-global::forms.button>
                            <a href="javascript:void(0);" onclick="canvasController.toggleMilestoneSelectors('hide');"
                                class="btn btn-secondary">
                                <i class="fas fa-times"></i> <?= $tpl->__('links.cancel') ?>
                            </a>
                        </div>
                    </div>

                    <div class="row" id="existingMilestone" style="display:none;">
                        <div class="col-md-12">
                            <x-global::forms.select name="existingMilestone" class="user-select" :labelText="__('input.placeholders.filter_by_milestone')"
                                data-placeholder="{{ __('input.placeholders.filter_by_milestone') }}">
                                <x-global::forms.select.select-option value="">
                                    {!! __('label.all_milestones') !!}
                                </x-global::forms.select.select-option>

                                @foreach ($tpl->get('milestones') as $milestoneRow)
                                    <x-global::forms.select.select-option :value="$milestoneRow->id" :selected="isset($searchCriteria['milestone']) &&
                                        $searchCriteria['milestone'] == $milestoneRow->id">
                                        {!! $milestoneRow->headline !!}
                                    </x-global::forms.select.select-option>
                                @endforeach
                            </x-global::forms.select>

                            <input type="hidden" name="type" value="milestone" />
                            <input type="hidden" name="articleId" value="<?php echo $id; ?> " />
                            <x-global::forms.button type="button"
                                onclick="jQuery('#primaryArticleSubmitButton').click()" class="btn btn-primary">
                                Save
                            </x-global::forms.button>
                            <a href="javascript:void(0);" onclick="canvasController.toggleMilestoneSelectors('hide');">
                                <i class="fas fa-times"></i> <?= $tpl->__('links.cancel') ?>
                            </a>
                        </div>
                    </div>

                </li>
                <?php
                    } else {

                        ?>

                <li class="ui-state-default" id="milestone_<?php echo $currentArticle->milestoneId; ?>" class="leanCanvasMilestone">

                    <div hx-trigger="load" hx-indicator=".htmx-indicator"
                        hx-get="{{ BASE_URL }}/hx/tickets/milestones/showCard?milestoneId=<?= $currentArticle->milestoneId ?>">
                        <div class="htmx-indicator">
                            <?= $tpl->__('label.loading_milestone') ?>
                        </div>
                    </div>
                    <a href="<?= CURRENT_URL ?>?removeMilestone=<?php echo $currentArticle->milestoneId; ?>"
                        class="{{ $canvasName }}CanvasModal delete formModal"><i class="fa fa-close"></i>
                        <?= $tpl->__('links.remove') ?></a>

                </li>
                <?php } ?>

            </ul>

            <?php } ?>

            <br />

        </div>
        <div class="col-md-8">


            <div class="btn-group inlineDropDownContainerLeft">
                {{-- <x-global::forms.button type="button" data-selected="graduation-cap"
                    class="icp icp-dd btn btn-default dropdown-toggle iconpicker-container titleIconPicker"
                    data-toggle="dropdown">
                    <span class="iconPlaceholder">
                        <i class="fa fa-file"></i>
                    </span>
                    <span class="caret"></span>
                </x-global::forms.button>
                <div class="dropdown-menu"></div> --}}

                {{-- <x-global::forms.material-emoji-picker name="articleIcon"  /> --}}
                <button data-selected="graduation-cap" type="button"
                    class="icp icp-dd btn btn-default dropdown-toggle iconpicker-container titleIconPicker mr-[10px]"
                    data-toggle="dropdown">
                    <span class="iconPlaceholder">
                        <i class="fa fa-file"></i>
                    </span>
                    <span class="caret"></span>
                </button>
                <div class="dropdown-menu"></div>
            </div>
            <input type="hidden" class="articleIcon" value="<?= $currentArticle->data ?>" name="articleIcon" />

            <x-global::forms.text-input type="text" name="title" class="main-title-input w-[80%]"
                value="{!! $tpl->escape($currentArticle->title) !!}" placeholder="{!! $tpl->__('input.placeholders.wiki_title') !!}" variant="title" />

            <br />
            <x-global::forms.text-input type="text" name="tags" id="tags" value="{!! $tpl->escape($currentArticle->tags) !!}" />

            <x-global::forms.text-editor name="description" :type="EditorTypeEnum::Complex->value" :value="$currentArticle->description" />

            <div class="row">
                <div class="col-md-10 padding-top-sm">
                    <br />
                    <input type="hidden" name="saveTicket" value="1" />
                    <input type="hidden" id="saveAndCloseButton" name="saveAndCloseArticle" value="0" />
                    <input type="submit" name="saveArticle" value="{{ __('buttons.save') }}"
                        id="primaryArticleSubmitButton" />
                    <input type="submit" class="btn btn-secondary" name="saveAndCloseArticle"
                        onclick="jQuery('#saveAndCloseButton').val('1');"
                        value="{{ __('buttons.save_and_close') }}" />



                </div>
                <div class="col-md-2 align-right padding-top-sm">
                    <?php if (isset($currentArticle->id) && $currentArticle->id != '' && $login::userIsAtLeast($roles::$editor)) { ?>
                    <br />
                    <a href="#/wiki/delArticle/<?php echo $currentArticle->id; ?>" class="delete"><i class="fa fa-trash"></i>
                        <?= $tpl->__('links.delete_article') ?></a>
                    <?php } ?>
                </div>
            </div>




        </div>
        <div class="col-md-2"></div>
    </div>




</x-global::content.modal.form>

<script type="module">
    import "@mix('/js/Domain/Tickets/Js/ticketsController.js')"

    jQuery(document).ready(function() {

        <?php if (isset($_GET['closeModal'])) { ?>
        jQuery.nmTop().close();
        <?php } ?>

        // jQuery('.iconpicker-container').iconpicker({
        //     //title: 'Dropdown with picker',
        //     component: '.btn > .iconPlaceholder',
        //     input: '.articleIcon',
        //     inputSearch: true,
        //     defaultValue: "far fa-file-alt",
        //     selected: "<?= $currentArticle->data ?>",
        //     showFooter: false,
        //     searchInFooter: false,
        //     icons: [{
        //             title: "far fa-file-alt",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fab fa-accessible-icon",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "far fa-address-book",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-archive",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-asterisk",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-balance-scale",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-ban",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-bell",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-binoculars",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-birthday-cake",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-bolt",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-book",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-bookmark",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-briefcase",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-bug",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "far fa-building",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-bullhorn",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "far fa-calendar-alt",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-chart-bar",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-check-circle",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-chart-line",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-chess",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-cogs",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-comments",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-compass",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-database",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-envelope",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-exclamation-triangle",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-flask",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-globe",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-gem",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-graduation-cap",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-hand-spock",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-heart",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-home",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-image",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-info-circle",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-key",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-leaf",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-life-ring",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-lightbulb",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-link",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-location-arrow",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-lock",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-map",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-map-signs",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-money-bill-alt",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-paper-plane",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-paperclip",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-question-circle",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-quote-left",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-road",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-rocket",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-shopping-cart",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-sitemap",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-sliders-h",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-star",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-tachometer-alt",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-thermometer-half",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-thumbs-down",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-thumbs-up",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-trash-alt",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-trophy",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-user-circle",
        //             searchTerms: ['icons']
        //         },
        //         {
        //             title: "fas fa-utensils",
        //             searchTerms: ['icons']
        //         }
        //     ]

        // });
        // jQuery('.iconpicker-container').on('iconpickerSelected', function(event) {
        //     jQuery(".articleIcon").val(event.iconpickerValue);
        // });

    });
</script>
