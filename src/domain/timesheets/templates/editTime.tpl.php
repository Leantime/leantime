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
                <input type="text" name="term" placeholder="<?php echo $this->__('input.placeholders.search_type_hit_enter')?>" />
            </form>
            
            <div class="pageicon"><span class="iconfa-laptop"></span></div>
            <div class="pagetitle">
                <h5><?php echo $this->__('OVERVIEW'); ?></h5>
                <h1><?php echo $this->__('EDIT_TIME'); ?></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">

                <?php echo $this->displayNotification() ?>

<form action="" method="post" class="stdform">

<div class="widget">
   <h4 class="widgettitle"><?php echo $this->__('OVERVIEW'); ?></h4>
   <div class="widgetcontent">

    <?php echo $this->displayLink('timesheets.delTime', $this->__('DELETE'), array('id' => $_GET['id']), array('class'=>'btn btn-primary btn-rounded f-right')); ?>

<label for="projects"><?php echo $this->__('label.project')?></label>
<select name="projects" id="projects" onchange="removeOptions($('select#projects option:selected').val());">
    <option value="all"><?php echo $this->__('headline.all_projects'); ?></option>

    <?php foreach($this->get('allProjects') as $row) {
        echo'<option value="'.$row['id'].'"';
        if($row['id'] == $values['project']) { echo' selected="selected" ';
        }
        echo'>'.$row['name'].'</option>';
    }

    ?>
</select> <br />

<label for="tickets"><?php echo $this->__('label.ticket')?></label>
<select name="tickets" id="tickets">

    <?php foreach($this->get('allTickets') as $row) { 
        echo'<option class="'.$row['projectId'].'" value="'.$row['projectId'].'|'.$row['id'].'"';
        if($row['id'] == $values['ticket']) { echo' selected="selected" ';
        }
        echo'>'.$row['headline'].'</option>';
    } ?>
    
</select> <br />
<label for="kind"><?php echo $this->__('label.kind')?></label> <select id="kind"
    name="kind">
    <?php foreach($this->get('kind') as $row){
        echo'<option value="'.$row.'"';
        if($row == $values['kind']) { echo ' selected="selected"';
        }
        echo'>'.$lang[$row].'</option>';

    }
    ?>

</select><br />
<label for="date"><?php echo $this->__('label.date')?></label> <input type="text"
    id="datepicker" name="date" value="<?php echo $values['date'] ?>" size="7" />
<br />
<label for="hours"><?php echo $this->__('label.hours')?></label> <input
    type="text" id="hours" name="hours"
    value="<?php echo $values['hours'] ?>" size="7" /> <br />
<label for="description"><?php echo $this->__('label.description')?></label> <textarea
    rows="5" cols="50" id="description" name="description"><?php echo $values['description']; ?></textarea><br />
<br />
<br />
    <p class="stdformbutton">
    <input type="submit" value="<?php echo $this->__('buttons.save'); ?>"
    name="save" class="button" /></fieldset>
    </p>
    
</div>
</div>
</div>
</div>
</form>

