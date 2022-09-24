<?php
	$wikis = $this->get('wikis');
    $wikiHeadlines = $this->get('wikiHeadlines');

    $currentWiki = $this->get('currentWiki');
    $currentArticle = $this->get('currentArticle');


function createTreeView($array, $currentParent, $currLevel = 0, $prevLevel = -1, $tplObject = '') {

    foreach ($array as $headline) {
        if ((int)$currentParent === (int)$headline->parent) {
            if ($currLevel > $prevLevel) echo "
            <ul class='article-toc'> ";
                if ($currLevel == $prevLevel) echo "  ";
                echo '
               <li data-jstree=\'{"icon":"'.$headline->data.'"}\' id="treenode_'.$headline->id.'">&nbsp;<a href="'.BASE_URL.'/wiki/show/'.$headline->id.'">'.$headline->title.'';
                if($headline->status == "draft") {
                    echo" <em>".$tplObject->__('label.draft_parenth')."</em> ";
                }
               echo'</a>';

                if ($currLevel > $prevLevel) { $prevLevel = $currLevel; }
                $currLevel++;
                createTreeView ($array, $headline->id, $currLevel, $prevLevel, $tplObject);
                $currLevel--;
                }
                }
                if ($currLevel == $prevLevel) echo "</li>
            </ul>
            ";
            }

?>

<div class="pageheader">
    <div class="pageicon"><span class="fa fa-book"></span></div>
    <div class="pagetitle">

        <h5><?php $this->e($_SESSION["currentProjectClient"]); ?></h5>
        <h1><?php echo $this->__("headlines.documents"); ?></h1>

    </div>

</div>

<div class="maincontent">


        <?php echo $this->displayNotification(); ?>

        <div class="row">
            <?php if($wikis != false && count($wikis) > 0) {?>

            <div class="col-lg-3">
                <div class="maincontentinner">
                    <div class="row">
                        <div class="col-md-12">


                            <h5 class="subtitle"><?=$this->__('label.current_wiki') ?></h5>
                            <div class="form-group board-select wikiSelect">
                                <a href="javascript:void(0)" class="dropdown-toggle full-width-select" data-toggle="dropdown">
                                    <?php $this->e($currentWiki->title) ?> <i class="fa fa-caret-down"></i>
                                </a>

                                <ul class="dropdown-menu">
                                    <?php  if($login::userIsAtLeast($roles::$editor)) { ?>
                                        <li>
                                            <a class="wikiModal inlineEdit" href="<?=BASE_URL ?>/wiki/wikiModal/<?=$currentWiki->id ?>"><?=$this->__("link.edit_wiki") ?></a>
                                            <a class="wikiModal inlineEdit" href="<?=BASE_URL ?>/wiki/wikiModal/"><?=$this->__("link.new_wiki") ?></a>
                                        </li>
                                    <?php } ?>

                                    <li class='nav-header border'></li>
                                    <?php foreach($wikis as $wiki){?>
                                    <li>
                                       <a href="<?=BASE_URL."/wiki/show?setWiki=".$wiki->id ?>"><?=$wiki->title?></a>
                                    </li>
                                    <?php } ?>


                                </ul>
                            </div>

                        </div>
                    </div>

                    <div class="row">

                        <div class="col-md-12">

                            <?php if($wikis != false && count($wikis) > 0) {?>
                                <div class="creationLinks">
                                    <a class="articleModal inlineEdit" href="<?=BASE_URL ?>/wiki/articleDialog/"><i class="fa fa-plus"></i> <?=$this->__("link.create_article") ?></a>
                                </div>
                            <?php } ?>

                            <div id="article-toc-wrapper">


                                <?php

                                    createTreeView($wikiHeadlines, 0, 0,-1, $this);
                                ?>

                                <?php /*

                                   */?>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
            <div class="col-lg-9">
                <div class="maincontentinner">

                    <?php

                    if($currentArticle && $currentArticle->id != null){ ?>
                    <div class="row">
                        <div class="col-md-12">

                            <?php  if($login::userIsAtLeast($roles::$editor)) { ?>
                                <div class="right">
                                    <a class="articleModal btn btn-default" href="<?=BASE_URL?>/wiki/articleDialog/<?=$currentArticle->id; ?>"><?=$this->__('links.edit_article');?></a>
                                    <a class="btn btn-default" onclick="leantime.generalController.copyUrl(event);" href="<?=BASE_URL?>/wiki/show/<?=$currentArticle->id; ?>"><?=$this->__('links.copy_url');?></a>
                                </div>
                            <?php } ?>


                            <h1 class="articleHeadline">
                                <i class="<?=$currentArticle->data ?>"></i>
                                <?=$currentArticle->title?>
                            </h1>
                            <div class="articleMeta">
                                <?=sprintf($this->__('labels.createdBy_on'), $currentArticle->firstname, $currentArticle->lastname, $this->getFormattedDateString($currentArticle->created), $this->getFormattedDateString($currentArticle->modified)); ?>
                                <br />
                                <div class="tagsinput readonly">

                                    <?php
                                    $tagsArray = explode(",", $currentArticle->tags);
                                    foreach($tagsArray as $tag){
                                        echo"<span class='tag'><span>".$tag."</span></span>";
                                    }

                                    ?>
                                </div><br />



                            </div>
                            <div class="articleBody mce-content-body">
                                <?=$currentArticle->description; ?>
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
                                    $this->assign('formUrl', BASE_URL."/wiki/show/".$currentArticle->id."");
                                    $this->displaySubmodule('comments-generalComment') ;
                                    ?>
                                </form>
                            </div>

                        </div>
                    </div>

                    <?php }else{?>
                        <div class="row">
                            <div class="col-md-12">
                                <?php
                                echo"<div class='center'>";
                                    echo"<div  style='width:30%' class='svgContainer'>";
                                        echo file_get_contents(ROOT."/images/svg/undraw_book_reading_re_fu2c.svg");
                                        echo"</div>";
                                    echo"<br /><h4>".$this->__("headlines.no_articles_yet")."</h4>";

                                    if ($wikis != false && count($wikis) > 0) {
                                        echo "".$this->__("text.create_new_content")."<br /><br />
                                        <a href='".BASE_URL."/wiki/articleDialog/' class='articleModal inlineEdit btn btn-primary'><i class='fa fa-plus'></i> ".$this->__("link.create_article")."</a><br/><br/>";

                                    }else{
                                        echo "".$this->__("text.create_new_wiki")."<br /><br />
                                        <a href='".BASE_URL."/wiki/wikiModal/' class='wikiModal inlineEdit btn btn-primary'>".$this->__("link.new_wiki")."</a><br/><br/>";
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>



        </div>

</div>


<script type="text/javascript">

   jQuery(document).ready(function() {
       leantime.wikiController.initTree("#article-toc-wrapper", <?=$currentArticle->id ?>);
       leantime.wikiController.wikiModal();
       leantime.wikiController.articleModal();

       <?php if($login::userHasRole([$roles::$commenter])) { ?>
        leantime.generalController.enableCommenterForms();
       <?php }?>

    });

</script>
