<?php
defined('RESTRICTED') or exit('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$roles = $tpl->get('roles');
$values = $tpl->get('values');
$projects = $tpl->get('relations');
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

<h4 class="widgettitle title-light"><i class="fa fa-people-group"></i> <?php echo $tpl->__('headlines.new_user'); ?></h4>

<?php echo $tpl->displayNotification() ?>

<form action="<?= BASE_URL?>/users/newUser" method="post" class="stdform userEditModal formModal">
    <div class="row" style="width:800px;">
        <div class="col-md-7">

                <h4 class="widgettitle title-light"><?php echo $tpl->__('label.profile_information'); ?></h4>

                    <label for="firstname"><?php echo $tpl->__('label.firstname'); ?></label> <input
                        type="text" name="firstname" id="firstname"
                        value="<?php echo $values['firstname'] ?>" /><br />

                    <label for="lastname"><?php echo $tpl->__('label.lastname'); ?></label> <input
                        type="text" name="lastname" id="lastname"
                        value="<?php echo $values['lastname'] ?>" /><br />

            <label for="role"><?php echo $tpl->__('label.role'); ?></label>
            <select name="role" id="role">

                <?php foreach ($tpl->get('roles') as $key => $role) { ?>
                    <?php if ($login::userHasRole(\Leantime\Domain\Auth\Models\Roles::$manager) && $key > 30) {
                        continue;
                    }?>
                        <option value="<?php echo $key; ?>"
                        <?php if ($key == $values['role']) {
                            ?> selected="selected" <?php
                        } ?>>
                        <?= $tpl->__('label.roles.'.$role) ?>
                    </option>
                <?php } ?>

            </select> <br />

            <label for="client"><?php echo $tpl->__('label.client') ?></label>
            <select name='client' id="client">
                <?php if ($login::userIsAtLeast('admin')) {?>
                    <option value="0" selected="selected"><?php echo $tpl->__('label.no_clients') ?></option>
                <?php } ?>
                <?php foreach ($tpl->get('clients') as $client) { ?>
                    <?php if ($login::userHasRole(\Leantime\Domain\Auth\Models\Roles::$manager) && $client['id'] !== session('userdata.clientId')) {
                        continue;
                    }
                    ?>
                    <option value="<?php echo $client['id'] ?>"
                            <?php if ($client['id'] == $values['clientId'] || $tpl->get('preSelectedClient') == $client['id']) {
                                ?>selected="selected"<?php
                            } ?>><?php $tpl->e($client['name']) ?></option>
                <?php } ?>
            </select><br/>
            <br/>


                <h4 class="widgettitle title-light"><?php echo $tpl->__('label.contact_information'); ?></h4>


                    <label for="user"><?php echo $tpl->__('label.email'); ?></label> <input
                        type="text" name="user" id="user" value="<?php echo $values['user'] ?>" /><br />

                    <label for="phone"><?php echo $tpl->__('label.phone'); ?></label> <input
                        type="text" name="phone" id="phone"
                        value="<?php echo $values['phone'] ?>" /><br />
            <br/>

            <h4 class="widgettitle title-light"><?php echo $tpl->__('label.employee_information'); ?></h4>
                <label for="jobTitle"><?php echo $tpl->__('label.jobTitle'); ?></label> <input
                    type="text" name="jobTitle" id="jobTitle" value="<?php echo $values['jobTitle'] ?>" /><br />

                <label for="jobLevel"><?php echo $tpl->__('label.jobLevel'); ?></label> <input
                    type="text" name="jobLevel" id="jobLevel" value="<?php echo $values['jobLevel'] ?>" /><br />

                <label for="department"><?php echo $tpl->__('label.department'); ?></label> <input
                    type="text" name="department" id="department" value="<?php echo $values['department'] ?>" /><br />


                    <p class="stdformbutton">
                        <input type="hidden" name="save" value="1" />
                        <input type="submit" name="save" id="save" value="<?php echo $tpl->__('buttons.invite_user'); ?>" class="button" />
                    </p>

        </div>
        <div class="col-md-5">

                <h4 class="widgettitle title-light"><?php echo $tpl->__('label.project_assignment'); ?></h4>

                <div class="scrollableItemList">
                    <?php
                    $currentClient = '';
$i = 0;
foreach ($tpl->get('allProjects') as $row) {
    if ($login::userHasRole(\Leantime\Domain\Auth\Models\Roles::$manager) && $row['clientId'] !== session('userdata.clientId')) {
        continue;
    }

    if ($row['clientName'] == '') {
        $row['clientName'] = 'Not assigned to client';
    }
    if ($currentClient != $row['clientName']) {
        if ($i > 0) {
            echo '</div>';
        }
        echo "<h3 id='accordion_link_".$i."'>
                            <a href='#' onclick='accordionToggle(".$i.");' id='accordion_toggle_".$i."'><i class='fa fa-angle-down'></i> ".$tpl->escape($row['clientName'])."</a>
                            </h3>
                            <div id='accordion_".$i."' class='simpleAccordionContainer'>";
        $currentClient = $row['clientName'];
    } ?>
                            <div class="item" style="padding:10px 0px;">
                                <input type="checkbox" name="projects[]" id='project_<?php echo $row['id'] ?>' value="<?php echo $row['id'] ?>"
                                <?php if (is_array($projects) === true && in_array($row['id'], $projects) === true) {
                                    echo "checked='checked';";
                                } ?>
                                />
                                <span class="projectAvatar" style="width:30px; float:left; margin-right:10px;">
                                    <img src='<?= BASE_URL ?>/api/projects?projectAvatar=<?= $row['id'] ?>&v=<?= format($row['modified'])->timestamp() ?>' />
                                </span>

                                <label for="project_<?php echo $row['id'] ?>" style="margin-top:-11px">
                                    <small><?php $tpl->e($row['type']); ?></small><br />
                                    <?php $tpl->e($row['name']); ?></label>
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
