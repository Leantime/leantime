<?php
    defined('RESTRICTED') or die('Restricted access');
    $program = $this->get('program');
    $bookedHours = $this->get('bookedHours');
    $state = $this->get('state');
?>

<div class="pageheader">
    <div class="pageicon"><span class="fa fa-suitcase"></span></div>
    <div class="pagetitle">
        <h5>Program Management</h5>
        <h1><?php echo sprintf("Program %s", $this->escape($program['name'])); ?>
        </h1>
    </div>
</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">

        <?php echo $this->displayNotification() ?>

        <div class="row">
            <div class="col-md-12">
                <div class="pull-right">

                </div>
            </div>
        </div>

        <div class="tabbedwidget tab-primary projectTabs">

            <ul>
                <li><a href="#projectdetails"><span class="fa fa-leaf"></span> Program Details</a></li>
                <li><a href="#team"><span class="fa fa-group"></span> <?php echo $this->__('tabs.team'); ?></a></li>

            </ul>

            <div id="projectdetails">
                <form action="" method="post" class="stdform">

                    <div class="row-fluid">

                        <div class="span8">
                            <div class="row-fluid">
                                <div class="span12">

                                    <div class="form-group">

                                        <input type="text" name="name" id="name" class="main-title-input" style="width:99%"  value="<?php $this->e($program['name']) ?>" placeholder="Enter the title of your program"/>

                                    </div>


                                    <input type="hidden" name="projectState"  id="projectState" value="0" />


                                </div>
                            </div>
                            <div class="row-fluid">
                                <div class="span12">
                                    <p>
                                        <?php echo $this->__('label.accomplish'); ?>
                                    </p>
                                    <textarea name="details" id="details" class="complexEditor" rows="5" cols="50"><?php echo $program['details'] ?></textarea>

                                </div>
                            </div>
                        </div>
                        <div class="span4">

                            <div class="row-fluid marginBottom">
                                <div class="span12">
                                    <h4 class="widgettitle title-light"><span
                                            class="fa fa-lock-open"></span><?php echo $this->__('labels.defaultaccess'); ?></h4>
                                    Who can access this program?
                                    <br /><br />

                                    <select name="globalProjectUserAccess" style="max-width:300px;">
                                        <option value="restricted" <?=$program['psettings'] == "restricted" ? "selected='selected'" : '' ?>><?php echo $this->__("labels.only_chose"); ?></option>
                                        <option value="clients" <?=$program['psettings'] == "clients" ? "selected='selected'" : ''?>><?php echo $this->__("labels.everyone_in_client"); ?></option>
                                        <option value="all" <?=$program['psettings'] == "all" ? "selected='selected'" : ''?>><?php echo $this->__("labels.everyone_in_org"); ?></option>
                                    </select>

                                </div>
                            </div>


                        </div>

                    </div>
                    <div class="row-fluid padding-top">
                        <?php if ($program['id'] != '') : ?>
                            <div class="pull-right padding-top">
                                <a href="<?=BASE_URL?>/pgmPro/delProgram/<?php echo $program['id']?>" class="delete"><i class="fa fa-trash"></i> <?php echo $this->__('buttons.delete'); ?></a>
                            </div>
                        <?php endif; ?>
                        <input type="submit" name="save" id="save" class="button" value="<?php echo $this->__('buttons.save'); ?>" class="button" />

                    </div>
                </form>
            </div>

            <div id="team">
                <form method="post" action="<?=BASE_URL ?>/pgmPro/showProgram/<?php echo $program['id']; ?>#team">
                    <input type="hidden" name="saveUsers" value="1" />


                    <div class="row-fluid">
                    <div class="span12">

                         <div class="form-group">
                             <br /><?=$this->__('text.choose_access_for_users'); ?><br />
                             <br />

                            <div class="row">
                                <div class="col-md-12">
                                    <h4 class="widgettitle title-light">
                                        <span class="fa fa-users"></span><?=$this->__('headlines.team_member'); ?>
                                    </h4>
                                </div>
                            </div>

                             <div class="row">
                                <?php foreach ($program['assignedUsers'] as $userId => $assignedUser) {?>
                                    <div class="col-md-4">
                                        <div class="userBox">
                                            <input type='checkbox' name='editorId[]' id="user-<?php echo $userId ?>" value='<?php echo $userId ?>'
                                                checked="checked"
                                                />
                                            <div class="commentImage">
                                                <img src="<?= BASE_URL ?>/api/users?profileImage=<?=$userId ?>"/>
                                            </div>
                                            <label for="user-<?php echo $userId ?>" ><?php printf($this->__('text.full_name'), $this->escape($assignedUser['firstname']), $this->escape($assignedUser['lastname'])); ?> <?php if ($assignedUser['status'] == 'i') {
                                                echo "<small>(" . $this->__('label.invited') . ")</small>";
                                                             } ?></label>
                                            <?php
                                            if (($roles::getRoles()[$assignedUser['role']] == $roles::$admin || $roles::getRoles()[$assignedUser['role']] == $roles::$owner)) { ?>
                                                <input type="text" readonly disabled value="<?php echo $this->__("label.roles." . $roles::getRoles()[$assignedUser['role']]) ?>" />
                                            <?php } else { ?>
                                                <select name="userProjectRole-<?php echo $userId ?>">
                                                    <option value="inherit">Inherit</option>
                                                    <option value="<?php echo array_search($roles::$readonly, $roles::getRoles()); ?>"
                                                        <?php if ($assignedUser['projectRole'] == array_search($roles::$readonly, $roles::getRoles())) {
                                                            echo" selected='selected' ";
                                                        }?>
                                                    ><?php echo $this->__("label.roles." . $roles::$readonly) ?></option>

                                                    <option value="<?php echo array_search($roles::$commenter, $roles::getRoles()); ?>"
                                                        <?php if ($assignedUser['projectRole'] == array_search($roles::$commenter, $roles::getRoles())) {
                                                            echo" selected='selected' ";
                                                        }?>
                                                    ><?php echo $this->__("label.roles." . $roles::$commenter) ?></option>
                                                    <option value="<?php echo array_search($roles::$editor, $roles::getRoles()); ?>"
                                                        <?php if ($assignedUser['projectRole'] == array_search($roles::$editor, $roles::getRoles())) {
                                                            echo" selected='selected' ";
                                                        }?>
                                                    ><?php echo $this->__("label.roles." . $roles::$editor) ?></option>
                                                    <option value="<?php echo array_search($roles::$manager, $roles::getRoles()); ?>"
                                                        <?php if ($assignedUser['projectRole'] == array_search($roles::$manager, $roles::getRoles())) {
                                                            echo" selected='selected' ";
                                                        }?>
                                                    ><?php echo $this->__("label.roles." . $roles::$manager) ?></option>
                                                </select>
                                            <?php } ?>
                                            <div class="clearall"></div>
                                        </div>
                                    </div>
                                <?php } ?>

                             </div>


                `           <div class="row">
                                <div class="col-md-12">
                                    <h4 class="widgettitle title-light">
                                        <span class="fa fa-user-friends "></span> Assign users to program
                                    </h4>
                                </div>
                            </div>

                             <div class="row">
                                <?php foreach ($this->get('availableUsers') as $row) { ?>
                                    <?php if (!isset($program['assignedUsers'][$row['id']])) { ?>
                                        <div class="col-md-4">
                                            <div class="userBox">
                                                <input type='checkbox' name='editorId[]' id="user-<?php echo $row['id'] ?>" value='<?php echo $row['id'] ?>' />

                                                <div class="commentImage">
                                                    <img src="<?= BASE_URL ?>/api/users?profileImage=<?=$row['id'] ?>"/>
                                                </div>
                                                <label for="user-<?php echo $row['id'] ?>" ><?php printf($this->__('text.full_name'), $this->escape($row['firstname']), $this->escape($row['lastname'])); ?></label>
                                                <?php if ($roles::getRoles()[$row['role']] == $roles::$admin || $roles::getRoles()[$row['role']] == $roles::$owner) { ?>
                                                    <input type="text" readonly disabled value="<?php echo $this->__("label.roles." . $roles::getRoles()[$row['role']]) ?>" />
                                                <?php } else { ?>
                                                    <select name="userProjectRole-<?php echo $row['id'] ?>">
                                                        <option value="inherit">Inherit</option>
                                                        <option value="<?php echo array_search($roles::$readonly, $roles::getRoles()); ?>"
                                                        <?php if (isset($program['assignedUsers'][$row['id']]) && $program['assignedUsers'][$row['id']] == array_search($roles::$readonly, $roles::getRoles())) {
                                                            echo" selected='selected' ";
                                                        }?>
                                                            ><?php echo $this->__("label.roles." . $roles::$readonly) ?></option>

                                                        <option value="<?php echo array_search($roles::$commenter, $roles::getRoles()); ?>"
                                                            <?php if (isset($program['assignedUsers'][$row['id']]) && $program['assignedUsers'][$row['id']] == array_search($roles::$commenter, $roles::getRoles())) {
                                                                echo" selected='selected' ";
                                                            }?>
                                                        ><?php echo $this->__("label.roles." . $roles::$commenter) ?></option>
                                                        <option value="<?php echo array_search($roles::$editor, $roles::getRoles()); ?>"
                                                            <?php if (isset($program['assignedUsers'][$row['id']]) && $program['assignedUsers'][$row['id']] == array_search($roles::$editor, $roles::getRoles())) {
                                                                echo" selected='selected' ";
                                                            }?>
                                                        ><?php echo $this->__("label.roles." . $roles::$editor) ?></option>
                                                        <option value="<?php echo array_search($roles::$manager, $roles::getRoles()); ?>"
                                                            <?php if (isset($program['assignedUsers'][$row['id']]) && $program['assignedUsers'][$row['id']] == array_search($roles::$manager, $roles::getRoles())) {
                                                                echo" selected='selected' ";
                                                            }?>
                                                        ><?php echo $this->__("label.roles." . $roles::$manager) ?></option>
                                                    </select>
                                                <?php } ?>
                                                <div class="clearall"></div>
                                            </div>
                                        </div>




                                    <?php }
                                } ?>
                                 <?php if ($login::userIsAtLeast($roles::$admin)) { ?>
                                     <div class="col-md-4">
                                         <div class="userBox">


                                                 <a class="userEditModal" href="<?=BASE_URL?>/users/newUser?preSelectProjectId=<?=$program['id'] ?>" style="font-size:var(--font-size-l); line-height:61px"><span class="fa fa-user-plus"></span> <?=$this->__('links.create_user'); ?></a>

                                             <div class="clearall"></div>
                                         </div>
                                     </div>
                                 <?php } ?>
                            </div>
                             <div class="row">
                                 <div class="col-md-12">

                                 </div>
                             </div>
                        </div>


                    </div>
                </div>
                    <br/>
                    <input type="submit" name="saveUsers" id="save" class="button" value="<?php echo $this->__('buttons.save'); ?>" class="button" />

                </form>

            </div>

        </div>
    </div>
</div>


<!-- New Status Template -->
<div class="newStatusTpl" style="display:none;">
    <div class="ticketBox">
    <div class="row statusList" id="todostatus-XXNEWKEYXX">
        <input type="hidden" name="labelKeys[]" id="labelKey-XXNEWKEYXX" class='labelKey' value="XXNEWKEYXX"/>
        <div class="sortHandle">
            <br />
            <span class="fa fa-sort"></span>
        </div>
        <div class="col-md-1">
            <label><?=$this->__("label.sortindex") ?></label>
            <input type="text" name="labelSort-XXNEWKEYXX" class="sorter" id="labelSort-XXNEWKEYXX" value="" style="width:30px;"/>
        </div>
        <div class="col-md-2">
            <label><?=$this->__("label.label") ?></label>
            <input type="text" name="label-XXNEWKEYXX" id="label-XXNEWKEYXX" value="" />

        </div>
        <div class="col-md-2">
            <label><?=$this->__("label.color") ?></label>
            <select name="labelClass-XXNEWKEYXX" id="labelClass-XXNEWKEYXX" class="colorChosen">
                <option value="label-blue" class="label-blue"><span class="label-blue"><?=$this->__('label.blue'); ?></span></option>
                <option value="label-info" class="label-info"><span class="label-info"><?=$this->__('label.dark-blue'); ?></span></option>
                <option value="label-darker-blue" class="label-darker-blue"><span class="label-darker-blue"><?=$this->__('label.darker-blue'); ?></span></option>
                <option value="label-warning" class="label-warning"><span class="label-warning"><?=$this->__('label.yellow'); ?></span></option>
                <option value="label-success" class="label-success"><span class="label-success"><?=$this->__('label.green'); ?></span></option>
                <option value="label-dark-green" class="label-dark-green"><span class="label-dark-green"><?=$this->__('label.dark-green'); ?></span></option>
                <option value="label-important" class="label-important"><span class="label-important"><?=$this->__('label.red'); ?></span></option>
                <option value="label-danger" class="label-danger"><span class="label-danger"><?=$this->__('label.dark-red'); ?></span></option>
                <option value="label-pink" class="label-pink"><span class="label-pink"><?=$this->__('label.pink'); ?></span></option>
                <option value="label-purple" class="label-purple"><span class="label-purple"><?=$this->__('label.purple'); ?></span></option>
                <option value="label-brown" class="label-brown"><span class="label-brown"><?=$this->__('label.brown'); ?></span></option>
                <option value="label-default" class="label-default"><span class="label-default"><?=$this->__('label.grey'); ?></span></option>
            </select>
        </div>
        <div class="col-md-2">
            <label><?=$this->__("label.reportType") ?></label>
            <select name="labelType-XXNEWKEYXX" id="labelType-XXNEWKEYXX">
                <option value="NEW"><?=$this->__('status.new'); ?></option>
                <option value="INPROGRESS"><?=$this->__('status.in_progress'); ?></option>
                <option value="DONE"><?=$this->__('status.done'); ?></option>
                <option value="NONE"><?=$this->__('status.dont_report'); ?></option>
            </select>
        </div>
        <div class="col-md-2">
            <label for=""><?=$this->__('label.showInKanban'); ?></label>
            <input type="checkbox" name="labelKanbanCol-XXNEWKEYXX" id="labelKanbanCol-XXNEWKEYXX"/>
        </div>
        <div class="remove">
            <br />
            <a href="javascript:void(0);" onclick="leantime.projectsController.removeStatus('XXNEWKEYXX')" class="delete"><span class="fa fa-trash"></span></a>
        </div>
    </div>
</div>
</div>

<script type='text/javascript'>

    jQuery(document).ready(function() {
        <?php if (isset($_GET['integrationSuccess'])) {?>
            window.history.pushState({},document.title, '<?=BASE_URL ?>/pgmPro/showProgram/<?php echo (int)$program['id']; ?>');
        <?php } ?>

        leantime.projectsController.initProjectTabs();
        leantime.projectsController.initDuplicateProjectModal();
        leantime.projectsController.initTodoStatusSortable("#todoStatusList");
        leantime.projectsController.initSelectFields();
        leantime.usersController.initUserEditModal();

    });

</script>
