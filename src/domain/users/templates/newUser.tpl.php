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

<h4 class="widgettitle title-light"><i class="fa fa-people-group"></i> <?php echo $this->__('headlines.new_user'); ?></h4>

<?php echo $this->displayNotification() ?>

<form action="<?=BASE_URL?>/users/newUser" method="post" class="stdform userEditModal">
    <div class="row">
        <div class="col-md-7">

                <h4 class="widgettitle title-light"><?php echo $this->__('label.profile_information'); ?></h4>

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

                    <p class="stdformbutton">
                        <input type="hidden" name="save" value="1" />
                        <input type="submit" name="save" id="save" value="<?php echo $this->__('buttons.invite_user'); ?>" class="button" />
                    </p>

        </div>
        <div class="col-md-5">

                <h4 class="widgettitle title-light"><?php echo $this->__('label.project_assignment'); ?></h4>

                <div class="scrollableItemList">
                    <?php
                    $currentClient = '';
                    $i = 0;
                    foreach($this->get('allProjects') as $row){

                        if($currentClient != $row['clientName']){
                            if($i>0) { echo"</div>"; }
                            echo "<h3 id='accordion_link_".$i."'>
                            <a href='#' onclick='accordionToggle(".$i.");' id='accordion_toggle_".$i."'><i class='fa fa-angle-down'></i> ".$this->escape($row['clientName'])."</a>
                            </h3>
                            <div id='accordion_".$i."' class='simpleAccordionContainer'>";
                            $currentClient = $row['clientName'];
                        } ?>
                            <div class="item">
                                <input type="checkbox" name="projects[]" id='project_<?php echo $row['id'] ?>' value="<?php echo $row['id'] ?>"
                                <?php if(is_array($projects) === true && in_array($row['id'], $projects) === true) { echo "checked='checked';"; } ?>
                                /><label for="project_<?php echo $row['id'] ?>"><?php $this->e($row['name']); ?></label>
                                <div class="clearall"></div>
                            </div>
                        <?php $i++; ?>
                    <?php } ?>

                </div>


        </div>
    </div>
</form>

<script>
    function accordionToggle(id) {

        let currentLink = jQuery("#accordion_toggle_"+id).find("i.fa");

        if(currentLink.hasClass("fa-angle-right")){
            currentLink.removeClass("fa-angle-right");
            currentLink.addClass("fa-angle-down");
            jQuery('#accordion_'+id).slideDown("fast");
        }else{
            currentLink.removeClass("fa-angle-down");
            currentLink.addClass("fa-angle-right");
            jQuery('#accordion_'+id).slideUp("fast");
        }

    }
</script>
