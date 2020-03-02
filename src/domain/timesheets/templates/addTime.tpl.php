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
                
                dateFormat: 'dd.mm.yy',
                dayNames: [<?php echo''.$language->lang_echo('DAYNAMES').'' ?>],
                dayNamesMin:  [<?php echo''.$language->lang_echo('DAYNAMES_MIN').'' ?>],
                monthNames: [<?php echo''.$language->lang_echo('MONTHS').'' ?>]
            });
           
            
        } 
    ); 
    
</script>


<div class="pageheader">
            <form action="<?=BASE_URL ?>/index.php?act=tickets.showAll" method="post" class="searchbar">
                <input type="text" name="term" placeholder="To search type and hit enter..." />
            </form>
            
            <div class="pageicon"><span class="iconfa-laptop"></span></div>
            <div class="pagetitle">
                <h5><?php echo $language->lang_echo('OVERVIEW'); ?></h5>
                <h1><?php echo $language->lang_echo('MY_TIMESHEETS'); ?></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">


                <div class="fail"><?php if($this->get('info') != '') { ?> <span
                    class="info"><?php echo $lang[$this->get('info')] ?></span> <?php 
                                    } ?>
                
                </div>
                
                <div id="loader">&nbsp;</div>
                <form action="" method="post" class="stdform">
                
                <div class="row-fluid">
    <div class="span12">




        <div class="widget">
           <h4 class="widgettitle"><?php echo $language->lang_echo('OVERVIEW'); ?></h4>
           <div class="widgetcontent" style="min-height: 460px">
               
               
               
               
                <label for="projects"><?php echo $language->lang_echo('PROJECT') ?></label> <select
                    name="projects" id="projects"
                    onchange="removeOptions($('select#projects option:selected').val());">
                    
                    
                    
                    
                    <option value="all"><?php echo $language->lang_echo('ALL_PROJECTS'); ?></option>
                
                    <optgroup>
        <?php foreach($this->get('allProjects') as $row) {
            $currentClientName = $row['clientName'];
            if($currentClientName != $lastClientName) {
                echo'</optgroup><optgroup label="'.$currentClientName.'">';
            }
                        
            echo'<option value="'.$row['id'].'"';
            if($row['id'] == $values['project']) { echo' selected="selected" ';
            }
            echo'>'.$row['name'].'</option>';
                        
            $lastClientName = $row['clientName'];
        }
                
        ?>
                    </optgroup>
                </select> <br />
                
                <label for="tickets"><?php echo $language->lang_echo('TICKET') ?></label> 
                <select name="tickets" id="tickets">
                
        <?php foreach($this->get('allTickets') as $row) {
            echo'<option class="'.$row['projectId'].'" value="'.$row['projectId'].'|'.$row['id'].'"';
            if($row['id'] == $values['ticket']) { echo' selected="selected" ';
            }
            echo'>'.$row['headline'].'</option>';
        } ?>
                    
                </select> <br />
                <br />
                <label for="kind"><?php echo $language->lang_echo('KIND') ?></label> <select id="kind"
                    name="kind">
        <?php foreach($this->get('kind') as $row){
            echo'<option value="'.$row.'"';
            if($row == $values['kind']) { echo ' selected="selected"';
            }
            echo'>'.$language->lang_echo($row).'</option>';
                
        } ?>
                
                </select><br />
                <label for="date"><?php echo $language->lang_echo('DATE') ?></label> <input type="text"
                    id="date" name="date" value="<?php echo $values['date'] ?>" size="7" />
                <br />
                <label for="hours"><?php echo $language->lang_echo('HOURS') ?></label> <input
                    type="text" id="hours" name="hours"
                    value="<?php echo $values['hours'] ?>" size="7" /> <br />
                <label for="description"><?php echo $language->lang_echo('DESCRIPTION') ?></label> <textarea
                    rows="5" cols="50" id="description" name="description"><?php echo $values['description']; ?></textarea><br />
                <br />
                <br />
                <label for="invoicedEmpl"><?php echo $language->lang_echo('INVOICED') ?></label> <input
                    type="checkbox" name="invoicedEmpl" id="invoicedEmpl"
        <?php if(isset($values['invoicedEmpl']) === true && $values['invoicedEmpl'] == '1') { echo ' checked="checked"';
        } ?> />
        <?php echo $language->lang_echo('ONDATE') ?>&nbsp;<input type="text"
                    id="invoicedEmplDate" name="invoicedEmplDate"
                    value="<?php echo $values['invoicedEmplDate'] ?>" size="7" /><br />
                
                
        <?php if($_SESSION['userdata']['role'] == 'admin') { ?> <br />
                <label for="invoicedComp"><?php echo $language->lang_echo('INVOICED_COMP') ?></label> <input
                    type="checkbox" name="invoicedComp" id="invoicedComp"
            <?php if($values['invoicedComp'] == '1') { echo ' checked="checked"';
            } ?> />
            <?php echo $language->lang_echo('ONDATE') ?>&nbsp;<input type="text"
                    id="invoicedCompDate" name="invoicedCompDate"
                    value="<?php echo $values['invoicedCompDate'] ?>" size="7" /><br />
        <?php } ?> <input type="submit" value="<?php echo $language->lang_echo('SAVE'); ?>"
                    name="save" class="button" /> <input type="submit"
                    value="<?php echo $language->lang_echo('SAVE_NEW'); ?>" name="saveNew" class="button" />
                
                
                
                
                </form>
</div>
</div>
            </div>
        </div>