<?php
defined('RESTRICTED') or die('Restricted access');


$helper = $this->get('helper');
$values = $this->get('values');
?>
<script type="text/javascript">


    $(document).ready(function() 
        { 

            
        
            $("#date, #invoicedCompDate, #invoicedEmplDate").datepicker({
                
                dateFormat: 'dd.mm.yy',
                dayNames: [<?php echo''.$lang['DAYNAMES'].'' ?>],
                dayNamesMin:  [<?php echo''.$lang['DAYNAMES_MIN'].'' ?>],
                monthNames: [<?php echo''.$lang['MONTHS'].'' ?>]
            });
           
            
        } 
    ); 
    
</script>


<h1><?php echo $lang['EDIT_TIME']; ?></h1>

<div class="fail"><?php if($this->get('info') != '') { ?> <span
    class="info"><?php echo $lang[$this->get('info')] ?></span> <?php 
                    } ?>

</div>

<div id="loader">&nbsp;</div>
<form action="" method="post">
<fieldset ><legend><?php echo $lang['OVERVIEW']; ?></legend>


<label for="tickets"><?php echo $lang['TICKET']?></label><br /> <select
    name="tickets" id="tickets">

<?php foreach($this->get('allProjects') as $rowProject) {?>
    <optgroup label="<?php echo $rowProject['clientName']; ?> - <?php echo $rowProject['name']; ?>">
    <?php foreach($this->get('allTickets') as $row) {
        if($row['projectId'] == $rowProject['id']) {
            echo'<option class="'.$row['projectId'].'" value="'.$row['projectId'].'|'.$row['id'].'"';
            if($row['id'] == $values['ticket']) { echo' selected="selected" ';
            }
            echo'>'.$row['headline'].'</option>';
        }
    }?>
    </optgroup>
<?php } ?>
</select> <br />
<label for="kind"><?php echo $lang['KIND']?></label><br /> <select id="kind"
    name="kind">
    <?php foreach($this->get('kind') as $row){
        echo'<option value="'.$row.'"';
        if($row == $values['kind']) { echo ' selected="selected"';
        }
        echo'>'.$lang[$row].'</option>';

    }
    ?>

</select><br />
<label for="date"><?php echo $lang['DATE']?></label><br /> <input type="text"
    id="date" name="date" value="<?php echo $values['date'] ?>" size="7" />
<br />
<label for="hours"><?php echo $lang['HOURS']?></label><br /> <input
    type="text" id="hours" name="hours"
    value="<?php echo $values['hours'] ?>" size="7" /> <br />
<label for="description"><?php echo $lang['DESCRIPTION']?></label><br /> <textarea
    rows="5" cols="30" id="description" name="description"><?php echo $values['description']; ?></textarea><br />
<br />
<br />
<label for="invoicedEmpl"><?php echo $lang['INVOICED_EMPL']?></label><br /> <input
    type="checkbox" name="invoicedEmpl" id="invoicedEmpl"
    <?php if($values['invoicedEmpl'] == '1') { echo ' checked="checked"';
    } ?> />
    <?php echo $lang['ONDATE']?>&nbsp;<input type="text"
    id="invoicedEmplDate" name="invoicedEmplDate"
    value="<?php echo $values['invoicedEmplDate'] ?>" size="7" /><br />

    <?php if($_SESSION['userdata']['role'] == 'admin') { ?> <br />
<label for="invoicedComp"><?php echo $lang['INVOICED_COMP']?></label><br /> <input
    type="checkbox" name="invoicedComp" id="invoicedComp"
        <?php if($values['invoicedComp'] == '1') { echo ' checked="checked"';
        } ?> />
        <?php echo $lang['ONDATE']?>&nbsp;<input type="text"
    id="invoicedCompDate" name="invoicedCompDate"
    value="<?php echo $values['invoicedCompDate'] ?>" size="7" /><br />
    <?php } ?> <input type="submit" value="<?php echo $lang['EDIT']; ?>"
    name="save" class="button" />
    </fieldset>


</form>
<a href="index.php?act=timesheets.showMy" class="link"><?php echo $lang['BACK']?></a>
