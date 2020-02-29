<?php
defined('RESTRICTED') or die('Restricted access');
$project = $this->get('project');
?>


<div class="pageheader">
           
            
            <div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
            <div class="pagetitle">
                <h5>Administration</h5>
                <h1><?php echo "Delete Project"; ?></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">


<h4 class="widget widgettitle"><?php echo $lang['CONFIRM_DELETE']; ?></h4>
                <div class="widgetcontent">
                    
        <?php if($this->get('msg') !== '') { ?>
                        <span class="info">
            <?php echo $lang[$this->get('msg')]; ?><br />
                            <a href="<?=BASE_URL ?>/projects/showAll/">Back to all projects</a>
                        </span>
        <?php }else { ?>
                    
                    <form method="post">
                        <p><?php echo $lang['CONFIRM_DELETE_TEXT']; ?></p><br />
                        <input type="submit" value="<?php echo $lang['DELETE']; ?>" name="del" class="button" />
                    </form>
        <?php } ?>
                
                </div>
            </div>
        </div>
