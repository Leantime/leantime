<?php
    $status = $this->get('status');
    $values = $this->get('values');
    $projects = $this->get('relations');
?>

<?php echo $this->displayNotification(); ?>

<div class="pageheader">
    <div class="pageicon"><span class="fa <?php echo $this->getModulePicture() ?>"></span></div>
    <div class="pagetitle">
        <h5><?php echo $this->__('label.administration') ?></h5>
        <h1><?php echo $this->__('headlines.edit_user'); ?></h1>
    </div>
</div><!--pageheader-->

<form action="" method="post" class="stdform userEditModal">
        <input type="hidden" name="<?=$_SESSION['formTokenName']?>" value="<?=$_SESSION['formTokenValue']?>" />
        <div class="maincontent">
            <div class="row">
                <div class="col-md-7">
                    <div class="maincontentinner">
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
                        <input type="submit" name="save" id="save" value="<?php echo $this->__('buttons.save'); ?>" class="button" />
                    </p>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="maincontentinner">
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
