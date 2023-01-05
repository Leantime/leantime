<?php
    defined('RESTRICTED') or die('Restricted access');
    $client = $this->get('client');
?>

<?php $this->dispatchTplEvent('beforePageHeaderOpen'); ?>
<div class="pageheader">
    <?php $this->dispatchTplEvent('afterPageHeaderOpen'); ?>
    <div class="pageicon"><span class="fa <?php echo $this->getModulePicture() ?>"></span></div>
    <div class="pagetitle">
        <h5><?php echo $this->__('label.administration') ?></h5>
        <h1><?php echo sprintf($this->__('headline.delete_client'), $client['name']); ?></h1>
    </div>
    <?php $this->dispatchTplEvent('beforePageHeaderClose'); ?>
</div><!--pageheader-->
<?php $this->dispatchTplEvent('afterPageHeaderClose'); ?>

<div class="maincontent">
    <div class="maincontentinner">

        <?php echo $this->displayNotification() ?>

        <h4 class="widget widgettitle"><?php echo $this->__("subtitles.delete") ?></h4>
        <div class="widgetcontent">

            <form method="post">

                <?php $this->dispatchTplEvent('afterFormOpen'); ?>

                <p><?php echo $this->__('text.confirm_client_deletion'); ?><br /></p>
                <input type="submit" value="<?php echo $this->__('buttons.yes_delete'); ?>" name="del" class="button" />
                <a class="btn btn-primary" href="/clients/showClient/<?php echo $client['id'] ?>"><?php echo $this->__('buttons.back'); ?></a>

                <?php $this->dispatchTplEvent('beforeFormClose'); ?>

            </form>
        </div>

    </div>
</div>
