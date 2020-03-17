<?php
defined('RESTRICTED') or die('Restricted access');
$client = $this->get('client');
?>

<div class="pageheader">
    <div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
    <div class="pagetitle">
        <h5><?php echo $this->__('label.administration') ?></h5>
        <h1><?php echo sprintf($this->__('headline.delete_client'), $client['name']); ?></h1>
    </div>
</div><!--pageheader-->
        
<div class="maincontent">
    <div class="maincontentinner">

        <?php echo $this->displayNotification() ?>

        <h4 class="widget widgettitle"><?php echo $this->__("subtitles.delete") ?></h4>
        <div class="widgetcontent">

            <form method="post">

                <p><?php echo $this->__('text.confirm_client_deletion'); ?><br /></p>
                <input type="submit" value="<?php echo $this->__('buttons.yes_delete'); ?>" name="del" class="button" />
                <a class="btn btn-primary" href="/clients/showClient/<?php echo $client['id'] ?>"><?php echo $this->__('buttons.back'); ?></a>

            </form>
        </div>

    </div>
</div>