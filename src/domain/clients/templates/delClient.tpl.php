<?php
defined('RESTRICTED') or die('Restricted access');
$client = $this->get('client');
?>

<div class="pageheader">
            
            
            <div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
            <div class="pagetitle">
                <h5>Administration</h5>
                <h1><?php printf($language->lang_echo('DELETE_CLIENT'), $client['name']); ?></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">

<?php if($this->get('msg') === '') { ?>

    <form action="" method="post">

        <p><?php echo $lang['CONFIRM_DELETE_QUE']; ?><br />
        </p>
        <input type="submit" value="<?php echo $lang['DELETE']; ?>" name="del"
            class="button"></fieldset>
        
    </form>

<?php } else { ?>
    <span class="info"><?php echo $lang[$this->get('msg')]; ?></span>
<?php } ?>

            </div>
        </div>