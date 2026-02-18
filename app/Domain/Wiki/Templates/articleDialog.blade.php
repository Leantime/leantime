@php
    $currentArticle = $tpl->get('article');

    $wikiHL = $tpl->get('wikiHeadlines');

    $wikiHeadlines = [];

    // Adds to $wikiHeadlines all the options and suboptions from $wikiHL for articles with parent $parentId.
    // The method is called recursively.
    // $id: current page Id.
    // $parentId: the id of parent article.
    // $wikiHeadline: the output, ordered array. will be modified inside the method.
    // $wikiHL: the array returned from Service.getAllWikiHeadlines.
    //          Even if it is passed by reference for performance the method will not modify it.
    // $indent is the string to put before the title, any level will add a space.
    function createTree($id, $parentId, &$wikiHeadlines, &$wikiHL, $indent)
    {
        // Finds the first article
        $articles = array_filter($wikiHL, function ($v) use ($parentId) {
            return $v->parent == $parentId;
        });
        if (count($articles) > 0) {
            usort($articles, function ($a1, $a2) {
                return $a1->title > $a2->title;
            });
            if ($parentId != null) {
                $indent = $indent.'-';
            }
            foreach ($articles as $article) {
                // This check prevents circular references by hiding the current page and its childs from list.
                if ($article->id != $id) {
                    $art = $article;
                    $art->title = $indent.$article->title;
                    $wikiHeadlines[] = $art;
                    createTree($id, $article->id, $wikiHeadlines, $wikiHL, $indent);
                }
            }
        }
    }

    // The following is the second original php block
    if (! isset($_GET['closeModal'])) {
        echo $tpl->displayNotification();
    }

    $id = '';
    if (isset($currentArticle->id)) {
        $id = $currentArticle->id;
    }

    // Populates the options tree
    createTree($id, null, $wikiHeadlines, $wikiHL, '');
@endphp

<form class="formModal" method="post" action="{{ CURRENT_URL }}">

    <div class="tw:grid tw:md:grid-cols-12 tw:gap-4">
        <div class="tw:md:col-span-2">
            <div class="marginBottom">
                <h4 class="widgettitle title-light">
                    <span class="fa fa-folder"></span>{{ __('subtitles.organization') }}
                </h4>
                <label>Parent</label>
                <x-global::forms.select name="parent" style="width:100%;">
                    <option value="0">None</option>
                    @foreach ($wikiHeadlines as $parent)
                        @if ($id != $parent->id)
                            <option value="{{ $parent->id }}"
                                    {{ ($parent->id == $currentArticle->parent) ? "selected='selected'" : '' }} >{{ e($parent->title) }}</option>
                        @endif
                    @endforeach
                </x-global::forms.select>

                <label>{{ __('label.status') }}</label>
                <x-global::forms.select name="status" style="width:100%;">
                    <option value="draft" {{ $currentArticle->status == 'draft' ? "selected='selected'" : '' }}>{{ __('label.draft') }}</option>
                    <option value="published" {{ $currentArticle->status == 'published' ? "selected='selected'" : '' }}>{{ __('label.published') }}</option>
                </x-global::forms.select>
            </div>

            @if ($id !== '')
                <h4 class="widgettitle title-light"><span class="fa fa-link"></span> {{ __('headlines.linked_milestone') }} <i class="fa fa-question-circle-o helperTooltip" data-tippy-content="{{ __('tooltip.link_milestones_tooltip') }}"></i></h4>

                <ul class="sortableTicketList" style="width:99%">
                    @if ($currentArticle->milestoneId == '')
                        <li class="ui-state-default tw:text-center" id="milestone_0">
                            <h4>{{ __('headlines.no_milestone_link') }}</h4>
                            {{ __('text.use_milestone_to_track_leancanvas') }}<br />
                            <div id="milestoneSelectors">
                                @if ($login::userIsAtLeast($roles::$editor))
                                    <a href="javascript:void(0);" onclick="leantime.leanCanvasController.toggleMilestoneSelectors('new');">{{ __('links.create_link_milestone') }}</a>
                                    | <a href="javascript:void(0);" onclick="leantime.leanCanvasController.toggleMilestoneSelectors('existing');">{{ __('links.link_existing_milestone') }}</a>
                                @endif
                            </div>
                            <div id="newMilestone" style="display:none;">
                                <textarea name="newMilestone"></textarea><br />
                                <input type="hidden" name="type" value="milestone" />
                                <input type="hidden" name="leancanvasitemid" value="{{ $id }} " />
                                <x-global::button tag="button" type="primary" onclick="jQuery('#primaryArticleSubmitButton').click()">{{ __('buttons.save') }}</x-global::button>
                                <x-global::button link="javascript:void(0);" type="secondary" onclick="leantime.leanCanvasController.toggleMilestoneSelectors('hide');" icon="fas fa-times">{{ __('links.cancel') }}</x-global::button>
                            </div>

                            <div id="existingMilestone" style="display:none;">
                                <select data-placeholder="{{ __('input.placeholders.filter_by_milestone') }}" name="existingMilestone"  class="user-select">
                                    <option value="">{{ __('label.all_milestones') }}</option>
                                    @foreach ($tpl->get('milestones') as $milestoneRow)
                                        <option value="{{ $milestoneRow->id }}"
                                            @if (isset($searchCriteria['milestone']) && ($searchCriteria['milestone'] == $milestoneRow->id))
                                                selected='selected'
                                            @endif
                                        >{{ $milestoneRow->headline }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="type" value="milestone" />
                                <input type="hidden" name="articleId" value="{{ $id }} " />
                                <x-global::button tag="button" type="primary" onclick="jQuery('#primaryArticleSubmitButton').click()">Save</x-global::button>
                                <a href="javascript:void(0);"  onclick="leantime.leanCanvasController.toggleMilestoneSelectors('hide');">
                                    <i class="fas fa-times"></i> {{ __('links.cancel') }}
                                </a>
                            </div>

                        </li>
                    @else
                        <li class="ui-state-default" id="milestone_{{ $currentArticle->milestoneId }}" class="leanCanvasMilestone" >

                            <div hx-trigger="load"
                                 hx-indicator=".htmx-indicator"
                                 hx-target="this"
                                 hx-swap="innerHTML"
                                 hx-get="{{ BASE_URL }}/hx/tickets/milestones/showCard?milestoneId={{ $currentArticle->milestoneId }}">
                                <div class="htmx-indicator">
                                    {{ __('label.loading_milestone') }}
                                </div>
                            </div>
                            <a href="{{ CURRENT_URL }}?removeMilestone={{ $currentArticle->milestoneId }}" class="formModal"><i class="fa fa-close"></i> {{ __('links.remove') }}</a>

                        </li>
                    @endif

                </ul>

            @endif

            <br />

        </div>
        <div class="tw:md:col-span-8">


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
            <input type="hidden" class="articleIcon" value="{{ $currentArticle->data }}" name="articleIcon"/>

            <x-global::forms.input name="title" class="main-title-input" value="{{ $tpl->escape($currentArticle->title) }}" placeholder="{{ __('input.placeholders.wiki_title') }}" style="width:80%" />

            <br />
            <input type="text" value="{{ e($currentArticle->tags) }}" name="tags" id="tags" />

            <textarea class="tiptapComplex" rows="20" cols="80" id="wikiArticleContentEditor"  name="description">{{ htmlentities($currentArticle->description ?? '') }}</textarea>


                <div class="tw:flex tw:justify-between tw:items-center tw:gap-4 padding-top-sm">
                    <div>
                        <br />
                        <input type="hidden" name="saveTicket" value="1" />
                        <input type="hidden" id="saveAndCloseButton" name="saveAndCloseArticle" value="0" />
                        <x-global::button submit type="primary" name="saveArticle" id="primaryArticleSubmitButton">{{ __('buttons.save') }}</x-global::button>
                        <x-global::button submit type="primary" name="saveAndCloseArticle" onclick="jQuery('#saveAndCloseButton').val('1');" outline>{{ __('buttons.save_and_close') }}</x-global::button>

                    </div>
                    <div class="tw:text-right padding-top-sm">
                        @if (isset($currentArticle->id) && $currentArticle->id != '' && $login::userIsAtLeast($roles::$editor))
                            <br />
                            <a href="#/wiki/delArticle/{{ $currentArticle->id }}" class="delete"><i class="fa fa-trash"></i> {{ __('links.delete_article') }}</a>
                        @endif
                    </div>
                </div>




        </div>
        <div class="tw:md:col-span-2"></div>
    </div>




</form>

<script>

    jQuery(document).ready(function(){

        if (window.leantime && window.leantime.tiptapController) {
            leantime.tiptapController.initComplexEditor();
        }

        @if (isset($_GET['closeModal']))
            jQuery.nmTop().close();
        @endif




        jQuery('.iconpicker-container').iconpicker({
            //title: 'Dropdown with picker',
            component:'.btn > .iconPlaceholder',
            input:'.articleIcon',
            inputSearch: true,
            defaultValue:"far fa-file-alt",
            selected: "{{ $currentArticle->data }}",
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
