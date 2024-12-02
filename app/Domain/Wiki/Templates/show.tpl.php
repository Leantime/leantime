<?php
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$wikis = $tpl->get('wikis');
$wikiHeadlines = $tpl->get('wikiHeadlines');

$currentWiki = $tpl->get('currentWiki');
$currentArticle = $tpl->get('currentArticle');

/**
 * @param $array
 * @param $currentParent
 * @param int                          $currLevel
 * @param int                          $prevLevel
 * @param \Leantime\Core\Template|null $tplObject
 * @return void
 */
function createTreeView($array, $currentParent, int $currLevel = 0, int $prevLevel = -1, ?\Leantime\Core\Template $tplObject = null): void
{

    foreach ($array as $headline) {
        if ((int)$currentParent === (int)$headline->parent) {
            if ($currLevel > $prevLevel) {
                echo "
            <ul class='article-toc'> ";
            }
            if ($currLevel == $prevLevel) {
                echo "  ";
            }
            echo '
               <li data-jstree=\'{"icon":"' . $headline->data . '"}\' id="treenode_' . $headline->id . '">&nbsp;<a href="' . BASE_URL . '/wiki/show/' . $headline->id . '">' . $tplObject->escape($headline->title) . '';
            if ($headline->status == "draft") {
                echo" <em>" . $tplObject->__('label.draft_parenth') . "</em> ";
            }
            echo'</a>';

            if ($currLevel > $prevLevel) {
                $prevLevel = $currLevel;
            }
            $currLevel++;
            createTreeView($array, $headline->id, $currLevel, $prevLevel, $tplObject);
            $currLevel--;
        }
    }
    if ($currLevel == $prevLevel) {
        echo "</li>
            </ul>
            ";
    }
}

?>

<div class="pageheader">
    <div class="pageicon"><span class="fa fa-book"></span></div>
    <div class="pagetitle">

        <h5><?php $tpl->e(session("currentProjectClient")); ?></h5>

        <?php if (count($wikis) > 0) {?>
            <span class="dropdown dropdownWrapper headerEditDropdown">
                <a href="javascript:void(0)" class="dropdown-toggle btn btn-transparent" data-toggle="dropdown"><i class="fa-solid fa-ellipsis-v"></i></a>
                <ul class="dropdown-menu editCanvasDropdown">
                    <?php if ($login::userIsAtLeast($roles::$editor) && $currentWiki) { ?>
                        <li><a class="inlineEdit" href="#/wiki/wikiModal/<?=$currentWiki->id ?>"><?=$tpl->__("link.edit_wiki") ?></a></li>
                        <li><a class="delete" href="#/wiki/delWiki/<?php echo $currentWiki->id; ?>" ><i class="fa fa-trash"></i> <?=$tpl->__('links.delete_wiki') ?></a></li>

                    <?php } ?>
                </ul>
            </span>
        <?php } ?>

        <h1><?php echo $tpl->__("headlines.documents"); ?>

         <?php if (count($wikis) > 0) {?>
             //
            <span class="dropdown dropdownWrapper">
                <a href="javascript:void(0)" class="dropdown-toggle header-title-dropdown" data-toggle="dropdown">
                    <?php
                    if ($currentWiki !== false) {
                        $tpl->e($currentWiki->title);
                    } else {
                        $tpl->__('label.select_board');
                    } ?>
                    <i class="fa fa-caret-down"></i>
                </a>

                <ul class="dropdown-menu">

                    <li><a class="inlineEdit" href="#/wiki/wikiModal/"><?=$tpl->__("link.new_wiki") ?></a></li>
                    <li class='nav-header border'></li>
                    <?php foreach ($wikis as $wiki) {?>
                        <li>
                            <a href="<?=BASE_URL . "/wiki/show?setWiki=" . $wiki->id ?>"><?=$tpl->escape($wiki->title)?></a>
                        </li>
                    <?php } ?>


                </ul>
            </span>
         <?php } ?>
        </h1>
    </div>

</div>





<div class="maincontent">


        <?php echo $tpl->displayNotification(); ?>

        <div class="row">

            <?php if ((!$currentArticle || $currentArticle->id != null) && (!$wikis || count($wikis) == 0)) { ?>
                <div class="col-md-12">
                    <div class="maincontentinner">
                        <?php
                        echo"<div class='center'>";
                        echo"<div  style='width:30%' class='svgContainer'>";
                        echo file_get_contents(ROOT . "/dist/images/svg/undraw_book_reading_re_fu2c.svg");
                        echo"</div>";
                        echo"<br /><h3>" . $tpl->__("headlines.no_articles_yet") . "</h3><br />";


                        echo "" . $tpl->__("text.create_new_wiki") . "<br /><br />
                                            <a href='#/wiki/wikiModal/' class='inlineEdit btn btn-primary'>" . $tpl->__("links.icon.create_new_board") . "</a><br/><br/>";
                        echo"</div>";
                        ?>
                    </div>
                </div>

            <?php } ?>

            <?php if ($wikis && count($wikis) > 0) {?>
                <div class="col-lg-12">

                    <?php

                    if ($currentArticle && $currentArticle->id != null) { ?>
                    <div class="row">

                        <div class="col-md-3">
                            <div class="row stickyRow">
                                    <div class="col-md-12" style="">
                                        <div class="maincontentinner">

                                            <h5 class="subtitle">Contents</h5>
                                            <div id="article-toc-wrapper">


                                            <?php

                                            createTreeView($wikiHeadlines, 0, 0, -1, $tpl);
                                            ?>

                                            <?php /*

                                       */?>
                                        </div>
                                            <?php if ($wikis && count($wikis) >= 1 && $login::userIsAtLeast($roles::$editor)) {?>
                                            <div class="creationLinks">
                                                <a class="inlineEdit " href="#/wiki/articleDialog/"><i class="fa fa-plus"></i> <?=$tpl->__("link.create_article") ?></a>
                                            </div>
                                            <?php } ?>

                                        </div>

                                    </div>
                            </div>
                        </div>
                        <div class="col-md-9" style="text-align: center">
                            <div class="maincontentinner">
                            <div class="articleWrapper">

                                <?php  if ($login::userIsAtLeast($roles::$editor)) { ?>
                                    <div class="right">
                                        <a class="btn btn-default round-button" href="#/wiki/articleDialog/<?=$currentArticle->id; ?>" ><i class='fa fa-edit'></i></a>
                                        <a class="dropdown-toggle btn btn-default round-button" data-toggle="dropdown" href="javascript:void(0)" onclick="leantime.snippets.copyToClipboard('<?=BASE_URL?>/wiki/show/<?=$currentArticle->id; ?>&projectId=<?=session("currentProject"); ?>')"><i class="fa fa-link"></i></a>


                                    </div>
                                <?php } ?>


                                <h1 class="articleHeadline">
                                    <i class="<?=$currentArticle->data ?>"></i>
                                    <?=$tpl->escape($currentArticle->title)?>
                                </h1>
                                <div class="articleMeta">
                                    <div class="metaContent">
                                    <?=sprintf($tpl->__('labels.createdBy_on'), $tpl->escape($currentArticle->firstname), $tpl->escape($currentArticle->lastname), format($currentArticle->created)->date(), format($currentArticle->modified)->date()); ?>
                                    <br />
                                    </div>
                                    <div class="tagsinput readonly">

                                        <?php
                                        $tagsArray = explode(",", $currentArticle->tags);
                                        if (count($tagsArray) >= 1) {
                                            echo "<i class='fa fa-tag pull-left' style='line-height:21px; margin-right:5px;'></i>&nbsp;";
                                        }

                                        foreach ($tagsArray as $tag) {
                                            echo"<span class='tag'><span>" . $tpl->escape($tag) . "</span></span>";
                                        }

                                        ?>
                                    </div><br />



                                </div>
                                <div class="articleBody mce-content-body centered">
                                    <?=$tpl->escapeMinimal($currentArticle->description); ?>
                                </div>

                                <?php if ($currentArticle->milestoneHeadline != '') { ?>
                                    <div class="milestonContainer border">
                                        <div hx-trigger="load"
                                             hx-indicator=".htmx-indicator"
                                             hx-get="<?=BASE_URL ?>/hx/tickets/milestones/showCard?milestoneId=<?=$currentArticle->milestoneId ?>">

                                            <div class="htmx-indicator">
                                                <?=$tpl->__("label.loading_milestone") ?>
                                            </div>
                                        </div>

                                </div><br /><br />
                                <?php } ?>

                                <div id="comments">
                                    <h4 class="widgettitle title-light"><span class="fa fa-comments"></span><?php echo $tpl->__('subtitles.discussion'); ?></h4>

                                    <form method="post" action="<?=BASE_URL ?>/wiki/show/<?php echo $currentArticle->id; ?>#comment">
                                        <input type="hidden" name="comment" value="1" />
                                        <?php
                                        $tpl->assign('formUrl', BASE_URL . "/wiki/show/" . $currentArticle->id . "");
                                        $tpl->displaySubmodule('comments-generalComment') ;
                                        ?>
                                    </form>
                                </div>

                            </div>
                            </div>

                        </div>
                    </div>

                    <?php } else {?>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="maincontentinner">
                                    <?php
                                    echo"<div class='center'>";
                                    echo"<div  style='width:30%' class='svgContainer'>";
                                    echo file_get_contents(ROOT . "/dist/images/svg/undraw_book_reading_re_fu2c.svg");
                                    echo"</div>";
                                    echo"<br /><h3>" . $tpl->__("headlines.no_articles_yet") . "</h3>";

                                    echo "" . $tpl->__("text.create_new_content") . "<br /><br />
                                            <a href='#/wiki/articleDialog/' class='inlineEdit btn btn-primary'><i class='fa fa-plus'></i> " . $tpl->__("link.create_article") . "</a><br/><br/>";


                                    echo"</div>";
                                    ?>
                                    </div>
                                </div>
                            </div>
                    <?php } ?>
                </div>



            <?php } ?>

            </div>

</div>



<script type="text/javascript">

   jQuery(document).ready(function() {
       <?php if ($currentArticle) {?>
        leantime.wikiController.initTree("#article-toc-wrapper", <?=$currentArticle->id ?>);
       <?php } ?>

       <?php if ($login::userHasRole([$roles::$commenter])) { ?>
        leantime.commentsController.enableCommenterForms();
       <?php }?>

    });

</script>
