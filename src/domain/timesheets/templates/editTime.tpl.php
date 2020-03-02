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

    jQuery(document).ready(function($) { 
        $("#datepicker").datepicker();
     });
</script>

<div class="pageheader">
            <form action="<?=BASE_URL ?>/index.php?act=tickets.showAll" method="post" class="searchbar">
                <input type="text" name="term" placeholder="To search type and hit enter..." />
            </form>
            
            <div class="pageicon"><span class="iconfa-laptop"></span></div>
            <div class="pagetitle">
                <h5><?php echo $language->lang_echo('OVERVIEW'); ?></h5>
                <h1><?php echo $language->lang_echo('EDIT_TIME'); ?></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">

                <?php echo $this->displayNotification() ?>

<form action="" method="post" class="stdform">

<div class="widget">
   <h4 class="widgettitle"><?php echo $language->lang_echo('OVERVIEW'); ?></h4>
   <div class="widgetcontent">

    <?php echo $this->displayLink('timesheets.delTime', $language->lang_echo('DELETE'), array('id' => $_GET['id']), array('class'=>'btn btn-primary btn-rounded f-right')); ?> 

<label for="projects"><?php echo $lang['PROJECT']?></label> 
<select name="projects" id="projects" onchange="removeOptions($('select#projects option:selected').val());">
    <option value="all"><?php echo $lang['ALL_PROJECTS']; ?></option>

    <?php foreach($this->get('allProjects') as $row) {
        echo'<option value="'.$row['id'].'"';
        if($row['id'] == $values['project']) { echo' selected="selected" ';
        }
        echo'>'.$row['name'].'</option>';
    }

    ?>
</select> <br />

<label for="tickets"><?php echo $lang['TICKET']?></label> 
<select name="tickets" id="tickets">

    <?php foreach($this->get('allTickets') as $row) { 
        echo'<option class="'.$row['projectId'].'" value="'.$row['projectId'].'|'.$row['id'].'"';
        if($row['id'] == $values['ticket']) { echo' selected="selected" ';
        }
        echo'>'.$row['headline'].'</option>';
    } ?>
    
</select> <br />
<label for="kind"><?php echo $lang['KIND']?></label> <select id="kind"
    name="kind">
    <?php foreach($this->get('kind') as $row){
        echo'<option value="'.$row.'"';
        if($row == $values['kind']) { echo ' selected="selected"';
        }
        echo'>'.$lang[$row].'</option>';

    }
    ?>

</select><br />
<label for="date"><?php echo $lang['DATE']?></label> <input type="text"
    id="datepicker" name="date" value="<?php echo $values['date'] ?>" size="7" />
<br />
<label for="hours"><?php echo $lang['HOURS']?></label> <input
    type="text" id="hours" name="hours"
    value="<?php echo $values['hours'] ?>" size="7" /> <br />
<label for="description"><?php echo $lang['DESCRIPTION']?></label> <textarea
    rows="5" cols="50" id="description" name="description"><?php echo $values['description']; ?></textarea><br />
<br />
<br />
<!--
<label for="invoicedEmpl"><?php echo $lang['INVOICED_EMPL']?></label> <input
    type="checkbox" name="invoicedEmpl" id="invoicedEmpl"
    <?php if($values['invoicedEmpl'] == '1') { echo ' checked="checked"';
    } ?> />
    <?php echo $lang['ONDATE']?>&nbsp;<input type="text"
    id="invoicedEmplDate" name="invoicedEmplDate"
    value="<?php echo $values['invoicedEmplDate'] ?>" size="7" /><br />

    <?php if($_SESSION['userdata']['role'] == 'admin') { ?> <br />
<label for="invoicedComp"><?php echo $lang['INVOICED_COMP']?></label> <input
    type="checkbox" name="invoicedComp" id="invoicedComp"
        <?php if($values['invoicedComp'] == '1') { echo ' checked="checked"';
        } ?> />
        <?php echo $lang['ONDATE']?>&nbsp;<input type="text"
    id="invoicedCompDate" name="invoicedCompDate"
    value="<?php echo $values['invoicedCompDate'] ?>" size="7" /><br />
    <?php } ?> 
-->    
    <p class="stdformbutton">
    <input type="submit" value="<?php echo $lang['EDIT']; ?>"
    name="save" class="button" /></fieldset>
    </p>
    
</div>
</div>
</div>
</div>
</form>

