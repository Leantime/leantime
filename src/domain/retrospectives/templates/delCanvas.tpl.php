<?php
    defined('RESTRICTED') or die('Restricted access');
?>

<div class="pageheader">


    <div class="pageicon"><span class="iconfa iconfa-trash"></span></div>
    <div class="pagetitle">
        <h5><?php echo $_SESSION['currentProjectClient']." // ". $_SESSION['currentProjectName']; ?></h5>
        <h1>Delete Board</h1>
    </div>
</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">

        <h4 class="widget widgettitle"><i class="iconfa iconfa-trash"></i> Delete</h4>
        <div class="widgetcontent">
            <form method="post" action="<?=BASE_URL ?>/retrospectives/delCanvas/<?php echo $_GET['id']?>">
                <p><?php echo $lang['CONFIRM_DELETE_CANVAS_ITEM']; ?></p><br />
                <input type="submit" value="Yes, delete!" name="del" class="button" />
                <a class="btn btn-secondary" href="<?=BASE_URL ?>/retrospectives/showBoards/"><?php echo $lang['BACK']; ?></a>
            </form>
        </div>

    </div>
</div>

