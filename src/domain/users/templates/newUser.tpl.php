<?php
defined('RESTRICTED') or die('Restricted access'); 
$roles = $this->get('roles');
$values = $this->get('values');
$projects = $this->get('relations');
?>

<script type="text/javascript">

    jQuery(document).ready(function(){
        // Dual Box Select
        var db = jQuery('#dualselect').find('.ds_arrow button');    //get arrows of dual select
        var sel1 = jQuery('#dualselect select#selectOrigin');        //get first select element
        var sel2 = jQuery('#dualselect select#selectDest');            //get second select element
        var projects = jQuery('#projects');
        //sel2.empty(); //empty it first from dom.

        db.click(function(){

            var t = (jQuery(this).hasClass('ds_prev'))? 0 : 1;    // 0 if arrow prev otherwise arrow next

            if(t) {

                sel1.find('option').each(function(){

                    if(jQuery(this).is(':selected')) {

                        jQuery(this).attr('selected',false);

                        sel2.append(jQuery(this).clone());

                        jQuery('#projects').append(jQuery(this));

                        jQuery('#projects option').attr("selected", "selected");

                    }

                });


            } else {
                sel2.find('option').each(function(){

                    if(jQuery(this).is(':selected')) {

                        jQuery(this).attr('selected',false);
                        index = jQuery(this).index();

                        sel1.append(jQuery(this));

                        jQuery('#projects option:eq('+index+')').remove();

                        jQuery('#projects option').attr("selected", "selected");

                    }
                });
            }


            return false;
        });


    });

</script>

<div class="pageheader">
                       
    <div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
    <div class="pagetitle">
        <h5><?php echo $this->__('label.administration') ?></h5>
        <h1><h1><?php echo $this->__('headlines.new_user'); ?></h1></h1>
    </div>
</div><!--pageheader-->
        
<div class="maincontent">
    <div class="maincontentinner">

        <?php echo $this->displayNotification() ?>
        <form action="" method="post" class="stdform">

            <div class="row-fluid">
            <span class="span6">
                <div class="widget">
                    <h4 class="widgettitle"><?php echo $this->__('label.overview'); ?></h4>
                    <div class="widgetcontent">
                        <label for="firstname"><?php echo $this->__('label.firstname'); ?></label> <input
                            type="text" name="firstname" id="firstname"
                            value="<?php echo $values['firstname'] ?>" /><br />

                        <label for="lastname"><?php echo $this->__('label.lastname'); ?></label> <input
                            type="text" name="lastname" id="lastname"
                            value="<?php echo $values['lastname'] ?>" /><br />

                        <label for="user"><?php echo $this->__('label.email'); ?></label> <input
                            type="text" name="user" id="user" value="<?php echo $values['user'] ?>" /><br />

                        <label for="phone"><?php echo $this->__('label.phone'); ?></label> <input
                            type="text" name="phone" id="phone"
                            value="<?php echo $values['phone'] ?>" /><br />

                        <label for="role"><?php echo $this->__('label.role'); ?></label>
                        <select name="role" id="role">

                                <?php foreach($this->get('roles') as $key => $role){ ?>
                                    <option value="<?php  echo $key; ?>"
                                        <?php if($key == $values['role']) { ?> selected="selected" <?php
                                        } ?>>
                                        <?=$this->__("label.roles.".$role) ?>
                                       </option>
                                <?php } ?>

                        </select> <br />

                        <label for="client"><?php echo $this->__('label.client') ?></label>
                        <select name='client' id="client">
                            <?php if($login::userIsAtLeast("manager")){?>
                                <option value="0" selected="selected"><?php echo $this->__('label.no_clients') ?></option>
                            <?php } ?>
                            <?php foreach($this->get('clients') as $client): ?>
                                <option value="<?php echo $client['id'] ?>" <?php if ($client['id'] == $values['clientId']) : ?>selected="selected"<?php
                               endif; ?>><?php $this->e($client['name']) ?></option>
                            <?php endforeach; ?>
                        </select><br/>

                        <label for="password"><?php echo $this->__('label.password'); ?></label> <input
                            type="password" name="password" id="password" value="" autocomplete="new-password"/><br />

                        <label for="password2"><?php echo $this->__('label.password_repeat'); ?></label> <input
                            type="password" name="password2" id="password2" value="" autocomplete="new-password"/><br />

                        <p class="stdformbutton">
                            <input type="submit" name="save" id="save" value="<?php echo $this->__('buttons.save'); ?>" class="button" />
                        </p>
                    </div>
                </div>
            </span>
            <span class="span6">
                <div class="widget ">
                        <h4 class="widgettitle"><?php echo $this->__('label.project_assignment'); ?></h4>
                        <div class="widgetcontent">

                             <span id="dualselect" class="dualselect" style="margin-left:0px;">

                                 <div class="row">
                                     <div class="col-5">
                                         <span><?php echo $this->__('label.available_projects'); ?></span>
                                          <select class="uniformselect" name="select3" multiple="multiple" size="10" id="selectOrigin" style="width:100%">

                                                            <?php foreach($this->get('allProjects') as $row){ ?>
                                                                <?php if(is_array($projects) === true && in_array($row['id'], $projects) === false) { ?>
                                                                    <option value="<?php echo $row['id'] ?>"><?php $this->e($row['name']); ?> / <?php $this->e($row['clientName']); ?></option>
                                                                <?php } ?>
                                                            <?php } ?>

                                                    </select>

                                     </div>
                                      <div class="col-2" class="align-center">
                                           <span class="ds_arrow">
                                                        <button class="btn ds_prev"><i class="iconfa-chevron-left"></i></button><br />
                                                        <button class="btn ds_next"><i class="iconfa-chevron-right"></i></button>
                                                    </span>

                                     </div>
                                      <div class="col-5">
                                          <span><?php echo $this->__('label.assigned_projects'); ?></span>
                                            <select name="select4" multiple="multiple" size="10" id="selectDest" style="width:100%">


                                                            <?php foreach($this->get('allProjects') as $row){ ?>
                                                                <?php if(is_array($projects) === true && in_array($row['id'], $projects) === true) { ?>
                                                                    <option value="<?php echo $row['id'] ?>"><?php echo $row['clientName']; ?> / <?php echo $row['name']; ?></option>

                                                                <?php } ?>

                                                            <?php } ?>
                                                    </select>
                                     </div>
                                 </div>





                                                </span>

                                                <select name="projects[]" multiple="multiple" size="10" id="projects" style="display:none;">


                                                            <?php foreach($this->get('allProjects') as $row){ ?>
                                                                <?php if(is_array($projects) === true && in_array($row['id'], $projects) === true) { ?>
                                                                    <option value="<?php echo $row['id'] ?>" selected="selected"><?php echo $row['name']; ?> / <?php echo $row['clientName']; ?></option>
                                                                <?php } ?>

                                                            <?php } ?>
                                                    </select>

                    </div>
                </div>
            </span>
            </div>
        </form>

    </div>
</div>
