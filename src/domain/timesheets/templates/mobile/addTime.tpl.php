<?php
defined('RESTRICTED') or die('Restricted access');


$helper = $this->get('helper');
$values = $this->get('values');
?>
<script type="text/javascript">
    function removeOptions(className){
    
        if(className != "all"){
            $('select#tickets option').attr('disabled', 'disabled');
            $('select#tickets option').css('display', 'none');
        
            $('.'+className).removeAttr('disabled');
            $('.'+className).css('display', 'list-item');
        }else{
            $('select#tickets option').css('display', 'list-item');
            $('select#tickets option').removeAttr('disabled');
        }
        
    }

    $(document).ready(function() 
        { 

            
        
            $("#date, #invoicedCompDate, #invoicedEmplDate").datepicker({
                
                dateFormat: <?php echo $this->__('language.dateFormat') ?>,
                dayNames: [<?php echo''.$this->__('language.dayNames').'' ?>],
                dayNamesMin:  [<?php echo''.$this->__('language.dayNamesMin').'' ?>],
                monthNames: [<?php echo''.$this->__('language.monthNames').'' ?>]
            });
           
            
        } 
    ); 
    
</script>


<h1><?php echo $this->__('headline.my_timesheets'); ?></h1>

<div class="fail"><?php if($this->get('info') != '') { ?> <span
    class="info"><?php echo $this->displayNotification() ?></span> <?php
                    } ?>

</div>

<div id="loader">&nbsp;</div>
<form action="" method="post">

<fieldset ><legend><?php echo $this->__('label.overview'); ?></legend>




<label for="tickets"><?php echo $this->__('label.overview') ?></label><br /> <select
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
<br />
<label for="kind"><?php echo $this->__('label.kind') ?></label><br /> <select id="kind"
    name="kind">
    <?php foreach($this->get('kind') as $row){
        echo'<option value="'.$row.'"';
        if($row == $values['kind']) { echo ' selected="selected"';
        }
        echo'>'.$lang[$row].'</option>';

    }
    ?>

</select><br />
<label for="date"><?php echo $this->__('label.date') ?></label><br /> <input type="text"
    id="date" name="date" value="<?php echo $values['date'] ?>" size="7" />
<br />
<label for="hours"><?php echo $this->__('label.hours') ?></label><br /> <input
    type="text" id="hours" name="hours"
    value="<?php echo $values['hours'] ?>" size="7" /> <br />
<label for="description"><?php echo $this->__('label.description') ?></label><br /> <textarea
    rows="5" cols="30" id="description" name="description"><?php echo $values['description']; ?></textarea><br />
<br />
<br />
<label for="invoicedEmpl"><?php echo $this->__('label.invoiced_emp') ?></label><br /> <input
    type="checkbox" name="invoicedEmpl" id="invoicedEmpl"
    <?php if(isset($values['invoicedEmpl']) === true && $values['invoicedEmpl'] == '1') { echo ' checked="checked"';
    } ?> />
    <?php echo $this->__('label.ondate')?>&nbsp;<input type="text"
    id="invoicedEmplDate" name="invoicedEmplDate"
    value="<?php echo $values['invoicedEmplDate'] ?>" size="7" /><br />


    <?php if($_SESSION['userdata']['role'] == 'admin') { ?> <br />
<label for="invoicedComp"><?php echo $this->__('label.invoiced_comp') ?></label><br /> <input
    type="checkbox" name="invoicedComp" id="invoicedComp"
        <?php if($values['invoicedComp'] == '1') { echo ' checked="checked"';
        } ?> />
        <?php echo $this->__('label.ondate')?>&nbsp;<input type="text"
    id="invoicedCompDate" name="invoicedCompDate"
    value="<?php echo $values['invoicedCompDate'] ?>" size="7" /><br />
    <?php } ?> <input type="submit" value="<?php echo $this->__('buttons.save') ?>"
    name="save" class="button" /> <input type="submit"
    value="<?php echo $this->__('buttons.save_and_new') ?>" name="saveNew" class="button" />



</fieldset>

<br />
</form>
<br />
<a href="index.php?act=timesheets.showAll" class="link"><?php echo $this->__('buttons.back') ?></a>
