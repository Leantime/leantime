@php
    $wikis = $tpl->get('wikis');
    $wikiHeadlines = $tpl->get('wikiHeadlines');

    $currentWiki = $tpl->get('currentWiki');
    $currentArticle = $tpl->get('currentArticle');

    function createTreeView($array, $currentParent, int $currLevel = 0, int $prevLevel = -1, ?\Leantime\Core\UI\Template $tplObject = null): void
    {

        foreach ($array as $headline) {
            if ((int) $currentParent === (int) $headline->parent) {
                if ($currLevel > $prevLevel) {
                    echo "
                <ul class='article-toc'> ";
                }
                if ($currLevel == $prevLevel) {
                    echo '  ';
                }
                echo '
                   <li data-jstree=\'{"icon":"'.$headline->data.'"}\' id="treenode_'.$headline->id.'">&nbsp;<a href="'.BASE_URL.'/wiki/show/'.$headline->id.'">'.$tplObject->escape($headline->title).'';
                if ($headline->status == 'draft') {
                    echo ' <em>'.$tplObject->__('label.draft_parenth').'</em> ';
                }
                echo '</a>';

                if ($currLevel > $prevLevel) {
                    $prevLevel = $currLevel;
                }
                $currLevel++;
                createTreeView($array, $headline->id, $currLevel, $prevLevel, $tplObject);
                $currLevel--;
            }
        }
        if ($currLevel == $prevLevel) {
            echo '</li>
                </ul>
                ';
        }
    }
@endphp

<div class="pageheader">
    <div class="pageicon"><span class="fa fa-book"></span></div>
    <div class="pagetitle">

        <h5>{{ e(session('currentProjectClient')) }}</h5>

        @if (count($wikis) > 0)
            <span class="dropdown dropdownWrapper headerEditDropdown">
                <a href="javascript:void(0)" class="dropdown-toggle btn btn-transparent" data-toggle="dropdown"><i class="fa-solid fa-ellipsis-v"></i></a>
                <ul class="dropdown-menu editCanvasDropdown">
                    @if ($login::userIsAtLeast($roles::$editor) && $currentWiki)
                        <li><a class="inlineEdit" href="#/wiki/wikiModal/{{ $currentWiki->id }}">{{ __('link.edit_wiki') }}</a></li>
                        <li><a class="delete" href="#/wiki/delWiki/{{ $currentWiki->id }}" ><i class="fa fa-trash"></i> {{ __('links.delete_wiki') }}</a></li>
                    @endif
                </ul>
            </span>
        @endif

        <h1>{{ __('headlines.documents') }}

         @if (count($wikis) > 0)
             //
            <span class="dropdown dropdownWrapper">
                <a href="javascript:void(0)" class="dropdown-toggle header-title-dropdown" data-toggle="dropdown">
                    @if ($currentWiki !== false)
                        {{ e($currentWiki->title) }}
                    @else
                        {{ __('label.select_board') }}
                    @endif
                    <i class="fa fa-caret-down"></i>
                </a>

                <ul class="dropdown-menu">

                    <li><a class="inlineEdit" href="#/wiki/wikiModal/">{{ __('link.new_wiki') }}</a></li>
                    <li class='nav-header border'></li>
                    @foreach ($wikis as $wiki)
                        <li>
                            <a href="{{ BASE_URL }}/wiki/show?setWiki={{ $wiki->id }}">{{ $tpl->escape($wiki->title) }}</a>
                        </li>
                    @endforeach


                </ul>
            </span>
         @endif
        </h1>
    </div>

</div>




<div class="maincontent">


        {!! $tpl->displayNotification() !!}

        <div class="row">

            @if ((! $currentArticle || $currentArticle->id != null) && (! $wikis || count($wikis) == 0))
                <div class="col-md-12">
                    <div class="maincontentinner">
                        @php
                        echo "<div class='center'>";
                        echo "<div  style='width:30%' class='svgContainer'>";
                        echo file_get_contents(ROOT.'/dist/images/svg/undraw_book_reading_re_fu2c.svg');
                        echo '</div>';
                        echo '<br /><h3>'.$tpl->__('headlines.no_articles_yet').'</h3><br />';

                        echo ''.$tpl->__('text.create_new_wiki')."<br /><br />
                                                    <a href='#/wiki/wikiModal/' class='inlineEdit btn btn-primary'>".$tpl->__('links.icon.create_new_board').'</a><br/><br/>';
                        echo '</div>';
                        @endphp
                    </div>
                </div>

            @endif

            @if ($wikis && count($wikis) > 0)
                <div class="col-lg-12">

                    @if ($currentArticle && $currentArticle->id != null)
                    <div class="row">

                        <div class="col-md-3">
                            <div class="row stickyRow">
                                    <div class="col-md-12" style="">
                                        <div class="maincontentinner">

                                            <h5 class="subtitle">Contents</h5>
                                            <div id="article-toc-wrapper">


                                            @php
                                                createTreeView($wikiHeadlines, 0, 0, -1, $tpl);
                                            @endphp

                                            </div>
                                            @if ($wikis && count($wikis) >= 1 && $login::userIsAtLeast($roles::$editor))
                                            <div class="creationLinks">
                                                <a class="inlineEdit " href="#/wiki/articleDialog/"><i class="fa fa-plus"></i> {{ __('link.create_article') }}</a>
                                            </div>
                                            @endif

                                        </div>

                                    </div>
                            </div>
                        </div>
                        <div class="col-md-9" style="text-align: center">
                            <div class="maincontentinner">
                            <div class="articleWrapper">

                                @if ($login::userIsAtLeast($roles::$editor))
                                    <div class="right">
                                        <a class="btn btn-default round-button" href="#/wiki/articleDialog/{{ $currentArticle->id }}" ><i class='fa fa-edit'></i></a>
                                        <a class="dropdown-toggle btn btn-default round-button" data-toggle="dropdown" href="javascript:void(0)" onclick="leantime.snippets.copyToClipboard('{{ BASE_URL }}/wiki/show/{{ $currentArticle->id }}&projectId={{ session('currentProject') }}')"><i class="fa fa-link"></i></a>


                                    </div>
                                @endif


                                <h1 class="articleHeadline">
                                    <i class="{{ $currentArticle->data }}"></i>
                                    {{ $tpl->escape($currentArticle->title) }}
                                </h1>
                                <div class="articleMeta">
                                    <div class="metaContent">
                                    {!! sprintf($tpl->__('labels.createdBy_on'), $tpl->escape($currentArticle->firstname), $tpl->escape($currentArticle->lastname), format($currentArticle->created)->date(), format($currentArticle->modified)->date()) !!}
                                    <br />
                                    </div>
                                    <div class="tagsinput readonly">

                                        @php
                                        $tagsArray = explode(',', $currentArticle->tags);
                                        if (count($tagsArray) >= 1) {
                                            echo "<i class='fa fa-tag pull-left' style='line-height:21px; margin-right:5px;'></i>&nbsp;";
                                        }

                                        foreach ($tagsArray as $tag) {
                                            echo "<span class='tag'><span>".$tpl->escape($tag).'</span></span>';
                                        }
                                        @endphp
                                    </div><br />



                                </div>
                                <div class="articleBody mce-content-body centered">
                                    {!! $tpl->escapeMinimal($currentArticle->description) !!}
                                </div>

                                @if ($currentArticle->milestoneHeadline != '')
                                    <div class="milestonContainer border">
                                        <div hx-trigger="load"
                                             hx-indicator=".htmx-indicator"
                                             hx-get="{{ BASE_URL }}/hx/tickets/milestones/showCard?milestoneId={{ $currentArticle->milestoneId }}">

                                            <div class="htmx-indicator">
                                                {{ __('label.loading_milestone') }}
                                            </div>
                                        </div>

                                </div><br /><br />
                                @endif

                                <div id="comments">
                                    <h4 class="widgettitle title-light"><span class="fa fa-comments"></span>{{ __('subtitles.discussion') }}</h4>

                                    <form method="post" action="{{ BASE_URL }}/wiki/show/{{ $currentArticle->id }}#comment">
                                        <input type="hidden" name="comment" value="1" />
                                        @php
                                            $tpl->assign('formUrl', BASE_URL.'/wiki/show/'.$currentArticle->id.'');
                                            $tpl->displaySubmodule('comments-generalComment');
                                        @endphp
                                    </form>
                                </div>

                            </div>
                            </div>

                        </div>
                    </div>

                    @else
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="maincontentinner">
                                    @php
                                    echo "<div class='center'>";
                                    echo "<div  style='width:30%' class='svgContainer'>";
                                    echo file_get_contents(ROOT.'/dist/images/svg/undraw_book_reading_re_fu2c.svg');
                                    echo '</div>';
                                    echo '<br /><h3>'.$tpl->__('headlines.no_articles_yet').'</h3>';

                                    echo ''.$tpl->__('text.create_new_content')."<br /><br />
                                                        <a href='#/wiki/articleDialog/' class='inlineEdit btn btn-primary'><i class='fa fa-plus'></i> ".$tpl->__('link.create_article').'</a><br/><br/>';

                                    echo '</div>';
                                    @endphp
                                    </div>
                                </div>
                            </div>
                    @endif
                </div>



            @endif

            </div>

</div>



<script type="text/javascript">

   jQuery(document).ready(function() {
       @if ($currentArticle)
        leantime.wikiController.initTree("#article-toc-wrapper", {{ $currentArticle->id }});
       @endif

       @if ($login::userHasRole([$roles::$commenter]))
        leantime.commentsController.enableCommenterForms();
       @endif

    });

</script>
