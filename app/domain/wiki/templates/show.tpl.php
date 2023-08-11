<?php
    $wikis = $this->get('wikis');
    $wikiHeadlines = $this->get('wikiHeadlines');

    $currentWiki = $this->get('currentWiki');
    $currentArticle = $this->get('currentArticle');


function createTreeView($array, $currentParent, $currLevel = 0, $prevLevel = -1, $tplObject = '')
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

        <h5><?php $this->e($_SESSION["currentProjectClient"]); ?></h5>

        <?php if (count($wikis) > 0) {?>
            <span class="dropdown dropdownWrapper headerEditDropdown">
                <a href="javascript:void(0)" class="dropdown-toggle btn btn-transparent" data-toggle="dropdown"><i class="fa-solid fa-ellipsis-v"></i></a>
                <ul class="dropdown-menu editCanvasDropdown">
                    <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
                        <li><a class="inlineEdit" href="#/wiki/wikiModal/<?=$currentWiki->id ?>"><?=$this->__("link.edit_wiki") ?></a></li>
                        <li><a class="delete wikiModal" href="<?=BASE_URL ?>/wiki/delWiki/<?php echo $currentWiki->id; ?>" ><i class="fa fa-trash"></i> <?=$this->__('links.delete_wiki') ?></a></li>

                    <?php } ?>
                </ul>
            </span>
        <?php } ?>

        <h1><?php echo $this->__("headlines.documents"); ?>

         <?php if (count($wikis) > 0) {?>
             //
            <span class="dropdown dropdownWrapper">
                <a href="javascript:void(0)" class="dropdown-toggle header-title-dropdown" data-toggle="dropdown">
                    <?php
                    if ($currentWiki !== false) {
                        $this->e($currentWiki->title);
                    } else {
                        $this->__('label.select_board');
                    } ?>
                    <i class="fa fa-caret-down"></i>
                </a>

                <ul class="dropdown-menu">

                    <li><a class="inlineEdit" href="#/wiki/wikiModal/"><?=$this->__("link.new_wiki") ?></a></li>
                    <li class='nav-header border'></li>
                    <?php foreach ($wikis as $wiki) {?>
                        <li>
                            <a href="<?=BASE_URL . "/wiki/show?setWiki=" . $wiki->id ?>"><?=$this->escape($wiki->title)?></a>
                        </li>
                    <?php } ?>


                </ul>
            </span>
         <?php } ?>
        </h1>
    </div>

</div>

<div class="maincontent">


        <?php echo $this->displayNotification(); ?>

        <div class="row">

            <?php if (($currentArticle == false || $currentArticle->id != null) && ($wikis == false || count($wikis) == 0)) { ?>
                <div class="col-md-12">
                    <div class="maincontentinner">
                        <?php
                        echo"<div class='center'>";
                        echo"<div  style='width:30%' class='svgContainer'>";
                        echo file_get_contents(ROOT . "/dist/images/svg/undraw_book_reading_re_fu2c.svg");
                        echo"</div>";
                        echo"<br /><h3>" . $this->__("headlines.no_articles_yet") . "</h3><br />";


                            echo "" . $this->__("text.create_new_wiki") . "<br /><br />
                                            <a href='#/wiki/wikiModal/' class='inlineEdit btn btn-primary'>" . $this->__("links.icon.create_new_board") . "</a><br/><br/>";
                        echo"</div>";
                        ?>
                    </div>
                </div>

            <?php } ?>

            <?php if ($wikis != false && count($wikis) > 0) {?>
                <div class="col-lg-12">
                    <div class="maincontentinner">
                    <?php

                    if ($currentArticle && $currentArticle->id != null) { ?>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="row stickyRow">

                                <div class="col-md-12" style="border-right:1px solid var(--neutral);">


                                    <h5 class="subtitle">Contents</h5>
                                    <div id="article-toc-wrapper">


                                        <?php

                                        createTreeView($wikiHeadlines, 0, 0, -1, $this);
                                        ?>

                                        <?php /*

                                   */?>
                                    </div>
                                    <?php if ($wikis != false && count($wikis) > 0 && $login::userIsAtLeast($roles::$editor)) {?>
                                        <div class="creationLinks">
                                            <a class="inlineEdit" href="#/wiki/articleDialog/"><i class="fa fa-plus"></i> <?=$this->__("link.create_article") ?></a>
                                        </div>
                                    <?php } ?>

                                </div>
                            </div>
                        </div>
                        <div class="col-md-9" style="text-align: center">

                            <div class="articleWrapper">

                                <?php  if ($login::userIsAtLeast($roles::$editor)) { ?>
                                    <div class="right">
                                        <a class="btn btn-default" href="#/wiki/articleDialog/<?=$currentArticle->id; ?>" ><i class='fa fa-edit'></i></a>
                                        <div class="dropdownWrapper pull-right" style="margin-left:10px;">
                                            <a class="dropdown-toggle btn btn-default" data-toggle="dropdown" href="<?=BASE_URL?>/wiki/show/<?=$currentArticle->id; ?>&projectId=<?=$_SESSION["currentProject"]; ?>"><i class="fa fa-link"></i></a>
                                            <div class="dropdown-menu padding-md">
                                                <input type="text" id="wikiURL" value="<?=BASE_URL?>/wiki/show/<?=$currentArticle->id; ?>&projectId=<?=$_SESSION["currentProject"]; ?>" />
                                                <button class="btn btn-primary" onclick="leantime.snippets.copyUrl('wikiURL');"><?=$this->__('links.copy_url') ?></button>
                                            </div>
                                        </div>

                                    </div>
                                <?php } ?>


                                <h1 class="articleHeadline">
                                    <i class="<?=$currentArticle->data ?>"></i>
                                    <?=$this->escape($currentArticle->title)?>
                                </h1>
                                <div class="articleMeta">
                                    <div class="metaContent">
                                    <?=sprintf($this->__('labels.createdBy_on'), $this->escape($currentArticle->firstname), $this->escape($currentArticle->lastname), $this->getFormattedDateString($currentArticle->created), $this->getFormattedDateString($currentArticle->modified)); ?>
                                    <br />
                                    </div>
                                    <div class="tagsinput readonly">

                                        <?php
                                        $tagsArray = explode(",", $currentArticle->tags);
                                        if (count($tagsArray) > 0) {
                                            echo "<i class='fa fa-tag pull-left' style='line-height:21px; margin-right:5px;'></i>&nbsp;";
                                        }

                                        foreach ($tagsArray as $tag) {
                                            echo"<span class='tag'><span>" . $this->escape($tag) . "</span></span>";
                                        }

                                        ?>
                                    </div><br />



                                </div>
                                <div class="articleBody mce-content-body">
                                    <?=$this->escapeMinimal($currentArticle->description); ?>
                                </div>

                                <?php if ($currentArticle->milestoneHeadline != '') { ?>
                                    <div class="milestonContainer border">
                                        <div class="row">

                                            <div class="col-md-5">
                                                <?php $this->e($currentArticle->milestoneHeadline); ?>
                                            </div>
                                            <div class="col-md-7" style="text-align:right">
                                                <?=sprintf($this->__("text.percent_complete"), $currentArticle->percentDone)?>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="progress">
                                                    <div class="progress-bar progress-bar-success" role="progressbar"
                                                         aria-valuenow="<?php echo $currentArticle->percentDone; ?>" aria-valuemin="0"
                                                         aria-valuemax="100" style="width: <?php echo $currentArticle->percentDone; ?>%">
                                                        <span class="sr-only"><?=sprintf($this->__("text.percent_complete"), $currentArticle->percentDone)?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                </div><br /><br />
                                <?php } ?>

                                <div id="comments">
                                    <h4 class="widgettitle title-light"><span class="fa fa-comments"></span><?php echo $this->__('subtitles.discussion'); ?></h4>

                                    <form method="post" action="<?=BASE_URL ?>/wiki/show/<?php echo $currentArticle->id; ?>#comment">
                                        <input type="hidden" name="comment" value="1" />
                                        <?php
                                        $this->assign('formUrl', BASE_URL . "/wiki/show/" . $currentArticle->id . "");
                                        $this->displaySubmodule('comments-generalComment') ;
                                        ?>
                                    </form>
                                </div>

                            </div>

                        </div>
                    </div>

                    <?php } else {?>
                            <div class="row">
                                <div class="col-md-12">
                                    <?php
                                    echo"<div class='center'>";
                                        echo"<div  style='width:30%' class='svgContainer'>";
                                            echo file_get_contents(ROOT . "/dist/images/svg/undraw_book_reading_re_fu2c.svg");
                                            echo"</div>";
                                        echo"<br /><h3>" . $this->__("headlines.no_articles_yet") . "</h3>";

                                            echo "" . $this->__("text.create_new_content") . "<br /><br />
                                            <a href='#/wiki/articleDialog/' class='inlineEdit btn btn-primary'><i class='fa fa-plus'></i> " . $this->__("link.create_article") . "</a><br/><br/>";


                                        echo"</div>";
                                    ?>
                                    </div>
                            </div>
                    <?php } ?>
                </div>
                </div>



            <?php } ?>

            </div>



        </div>

</div>


<script type="text/javascript">

   jQuery(document).ready(function() {
       <?php if ($currentArticle) {?>
        leantime.wikiController.initTree("#article-toc-wrapper", <?=$currentArticle->id ?>);
       <?php } ?>

       leantime.wikiController.wikiModal();

       <?php if ($login::userHasRole([$roles::$commenter])) { ?>
        leantime.commentsController.enableCommenterForms();
       <?php }?>

    });

</script>
