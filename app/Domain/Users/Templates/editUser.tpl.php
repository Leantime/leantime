<?php
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$status = $tpl->get('status');
$values = $tpl->get('values');
$projects = $tpl->get('relations');
?>

<?php echo $tpl->displayNotification(); ?>

<div class="pageheader">
    <div class="pageicon"><span class="fa <?php echo $tpl->getModulePicture() ?>"></span></div>
    <div class="pagetitle">
        <h5><?php echo $tpl->__('label.administration') ?></h5>
        <h1><?php echo $tpl->__('headlines.edit_user'); ?></h1>
    </div>
</div><!--pageheader-->

<form action="" method="post" class="stdform userEditModal">
        <input type="hidden" name="<?= session('formTokenName')?>" value="<?= session('formTokenValue')?>" />
        <div class="maincontent">
            <div class="row">
                <div class="col-md-7">
                    <div class="maincontentinner">
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
                            <option value="<?php echo $key; ?>"
                                <?php if ($key == $values['role']) {
                                    ?> selected="selected" <?php
                                } ?>>
                                <?= $tpl->__('label.roles.'.$role) ?>
                            </option>
                        <?php } ?>

                    </select> <br />

                    <label for="status"><?php echo $tpl->__('label.status'); ?></label>
                    <select name="status" id="status" class="pull-left">

                        <option value="a"
                            <?php if (strtolower($values['status']) == 'a') {
                                ?> selected="selected" <?php
                            } ?>>
                            <?= $tpl->__('label.active') ?>
                        </option>

                        <option value="i"
                            <?php if (strtolower($values['status']) == 'i') {
                                ?> selected="selected" <?php
                            } ?>>
                            <?= $tpl->__('label.invited') ?>
                        </option>

                        <option value=""
                            <?php if (strtolower($values['status']) == '') {
                                ?> selected="selected" <?php
                            } ?>>
                            <?= $tpl->__('label.deactivated') ?>
                        </option>


                    </select>
                        <?php if ($values['status'] == 'i') { ?>
                        <div class="pull-left dropdownWrapper" style="padding-left:5px; line-height: 29px;">
                            <a class="dropdown-toggle btn btn-default" data-toggle="dropdown" href="<?= BASE_URL ?>/auth/userInvite/<?= $values['pwReset'] ?>"><i class="fa fa-link"></i> <?= $tpl->__('label.copyinviteLink') ?></a>
                            <div class="dropdown-menu padding-md noClickProp">
                                <input type="text" id="inviteURL" value="<?= BASE_URL ?>/auth/userInvite/<?= $values['pwReset'] ?>" />
                                <button class="btn btn-primary" onclick="leantime.snippets.copyUrl('inviteURL');"><?= $tpl->__('links.copy_url') ?></button>
                            </div>
                            <a href="<?= BASE_URL?>/users/editUser/<?= $values['id'] ?>?resendInvite" class="btn btn-default" style="margin-left:5px;"><i class="fa fa-envelope"></i> <?= $tpl->__('buttons.resend_invite') ?></a>
                        </div>
                        <?php } ?>
                        <div class="clearfix"></div>




                    <label for="client"><?php echo $tpl->__('label.client') ?></label>
                    <select name='client' id="client">
                        <?php if ($login::userIsAtLeast('manager')) {?>
                            <option value="0" selected="selected"><?php echo $tpl->__('label.no_clients') ?></option>
                        <?php } ?>
                        <?php foreach ($tpl->get('clients') as $client) { ?>
                            <option value="<?php echo $client['id'] ?>" <?php if ($client['id'] == $values['clientId']) {
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
                            value="<?php echo $values['phone'] ?>" /><br /><br />


                        <h4 class="widgettitle title-light"><?php echo $tpl->__('label.employee_information'); ?></h4>
                        <label for="jobTitle"><?php echo $tpl->__('label.jobTitle'); ?></label> <input
                            type="text" name="jobTitle" id="jobTitle" value="<?php echo $values['jobTitle'] ?>" /><br />

                        <label for="jobLevel"><?php echo $tpl->__('label.jobLevel'); ?></label> <input
                            type="text" name="jobLevel" id="jobLevel" value="<?php echo $values['jobLevel'] ?>" /><br />

                        <label for="department"><?php echo $tpl->__('label.department'); ?></label> <input
                            type="text" name="department" id="department" value="<?php echo $values['department'] ?>" /><br />



                    <p class="stdformbutton">
                        <input type="submit" name="save" id="save" value="<?php echo $tpl->__('buttons.save'); ?>" class="button" />
                    </p>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="maincontentinner">
                    <h4 class="widgettitle title-light"><?php echo $tpl->__('label.project_assignment'); ?></h4>

                    <div class="scrollableItemList">
                        <?php
                        $currentClient = '';
$i = 0;
foreach ($tpl->get('allProjects') as $row) {
    if ($row['clientName'] == null) {
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
            </div>
        </div>
</form>

<script>

    jQuery(".noClickProp.dropdown-menu").on("click", function(e) {
        e.stopPropagation();
    });

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
