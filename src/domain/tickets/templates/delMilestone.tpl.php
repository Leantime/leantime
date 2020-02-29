<?php
defined('RESTRICTED') or die('Restricted access');

?>

<h4 class="widgettitle title-light"><i class="fa fa-rocket"></i> Delete Milestone</h4>

        <?php if($this->get('info') === '') { ?>
                    
                        <form method="post" action="<?=BASE_URL ?>/tickets/delMilestone/<?php echo $this->get('id') ?>">
                            <p>Are you sure you would like to delete this Milestone?</p><br />
                            <input type="submit" value="Yes, delete" name="del" class="button" />
                            <a class="btn btn-secondary" href="<?=BASE_URL ?>/tickets/roadmap/">No, Cancel</a>
                        </form>
                        
        <?php }else{ ?>
                    
                        <span class="info"><?php echo $lang[$this->get('info')] ?></span>
                    
        <?php } ?>
