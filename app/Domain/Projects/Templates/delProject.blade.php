@extends($layout)

@section('content')

<?php
$project = $tpl->get('project');
?>


<div class="pageheader">

    <div class="pageicon"><span class="fa <?php echo $tpl->getModulePicture() ?>"></span></div>
    <div class="pagetitle">
        <h5><?php echo $tpl->__('label.administration') ?></h5>
        <h1><?php echo sprintf($tpl->__('headlines.delete_project_x'), $project['name']); ?></h1>
    </div>
</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">

        @displayNotification()

        <h4 class="widget widgettitle"><?php echo $tpl->__("subtitles.delete") ?></h4>
        <div class="widgetcontent">

            <form method="post">
                <p><?php echo $tpl->__('text.confirm_project_deletion'); ?></p><br />
                <input type="submit" value="<?php echo $tpl->__('buttons.yes_delete'); ?>" name="del" class="button" />
                <a class="btn btn-primary" href="<?=BASE_URL ?>/projects/showProject/<?php echo $project['id'] ?>"><?php echo $tpl->__('buttons.back'); ?></a>
            </form>

        </div>


    </div>
</div>
