<?php
defined('RESTRICTED') or die('Restricted access');
$ticket = $this->get('ticket');

?>

<div class="pageheader">
           
            
            <div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
            <div class="pagetitle">
                <h5><?php echo $_SESSION['currentProjectClient']." // ". $_SESSION['currentProjectName']; ?></h5>
                <h1><?php echo "Delete Ticket"; ?></h1>

            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">
                
                <h4 class="widget widgettitle"><?php echo $lang['CONFIRM_DELETE']; ?></h4>
                <div class="widgetcontent">
        <?php if($this->get('info') === '') { ?>
                    
                        <form method="post">
                            <p><?php echo $lang['CONFIRM_DELETE_TICKET']; ?></p><br />
                            <input type="submit" value="<?php echo $lang['DELETE']; ?>" name="del" class="button" />
                            <a class="btn btn-primary" href="<?=BASE_URL ?>/tickets/showTicket/<?php echo $ticket['id']?>"><?php echo $lang['BACK']; ?></a>
                        </form>
                        
        <?php }else{ ?>
                    
                        <span class="info"><?php echo $lang[$this->get('info')] ?></span>
                    
        <?php } ?>
                </div>
            </div>
        </div>