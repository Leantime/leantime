<?php
    defined('RESTRICTED') or die('Restricted access');
    $ticket = $this->get("ticket");
?>

<?php if($ticket->type == "milestone"){?>
    <h4 class="widgettitle title-light"><?=$this->__("headline.move_milestone"); ?> </h4>
<?php }else{ ?>
    <h4 class="widgettitle title-light"><?=$this->__("headline.move_todo"); ?> </h4>
<?php } ?>


    <form method="post" action="<?=BASE_URL?>/tickets/moveTicket/<?=$ticket->id ?>" class="formModal">
        <h3>#<?=$ticket->id ?> - <?=$this->escape($ticket->headline); ?></h3> <br />
        <p><?php echo $this->__('text.moving'); ?><br />
           </p><br />

        <select id="projectSelector" name="projectId">
        <?php
        $i=0;
        foreach ($this->get('projects') as $projectRow) {
            if ($lastClient != $projectRow['clientName']) {
                $lastClient = $projectRow['clientName'];
                if($i > 1){ echo"</optgroup>"; }
                echo "<optgroup label='" . $this->escape($projectRow['clientName']) . "'> ";
            }
            echo "<option value='".$projectRow["id"]."'>" . $this->escape($projectRow["name"]) . "</option>";
            $i++;
        }
        ?>
        </select><br /><br /><br /><br />
        <br />
        <input type="submit" value="<?php echo $this->__('buttons.move'); ?>" name="move" class="button" />
        <a class="pull-right" href="javascript:void(0);" onclick="jQuery.nmTop().close();"><?php echo $this->__('buttons.back'); ?></a>
        <div class="clearall"></div>
        <br />
    </form>


<script>
    <?php if (isset($_GET['closeModal'])) { ?>
        jQuery.nmTop().close();
    <?php } ?>

    jQuery(document).ready(function(){
        jQuery("#projectSelector").chosen();
    });
</script>



