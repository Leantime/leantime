<?php
    defined('RESTRICTED') or die('Restricted access');
?>

<div class="pageheader">
    <div class="pageicon"><span class="fa fa-trash"></span></div>
    <div class="pagetitle">
        <h5><?php echo $_SESSION['currentProjectClient'] . " // " . $_SESSION['currentProjectName']; ?></h5>
        <h1><?=$this->__("headline.delete_board") ?></h1>
    </div>
</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">
        <h4 class="widget widgettitle"><?php echo $this->__("subtitles.delete") ?></h4>
        <div class="widgetcontent">
            <form method="post" action="<?=BASE_URL ?>/ideas/delCanvas/<?php echo $_GET['id']?>">
                <p><?php echo $this->__("text.are_you_sure_delete_idea_board") ?></p>
                <input type="submit" value="<?php echo $this->__("buttons.yes_delete")?>" name="del" class="button" />
                <a class="btn btn-secondary" href="<?=BASE_URL ?>/ideas/showBoards"><?php echo $this->__("buttons.back") ?></a>
            </form>
        </div>

    </div>
</div>

