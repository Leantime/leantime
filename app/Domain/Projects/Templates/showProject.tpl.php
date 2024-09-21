<?php
defined('RESTRICTED') or die('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$project = $tpl->get('project');
$state = $tpl->get('state');
?>

<div class="pageheader">
    <div class="pageicon"><span class="fa fa-suitcase"></span></div>
    <div class="pagetitle">
        <h5><?php echo $tpl->__('label.administration') ?></h5>
        <h1><?php echo sprintf($tpl->__('headline.project'), $tpl->escape($project['name'])); ?>
        </h1>
    </div>
</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">

        <?php echo $tpl->displayNotification() ?>

        <div class="inlineDropDownContainer" style="float:right; z-index:9; padding-top:2px;">

            <a href="<?=BASE_URL?>/projects/duplicateProject/<?=$project['id']?>" class="btn btn-default duplicateProjectModal" data-tippy-content="<?=$tpl->__("link.duplicate_project") ?>"><i class="fa-regular fa-copy"></i> Copy</a>
            <a href="<?=BASE_URL?>/projects/delProject/<?=$project['id']?>" data-tippy-content="<?=$tpl->__("link.delete_project") ?>" class="btn btn-danger-outline delete"><i class="fa fa-trash"></i> Delete</a>


        </div>
        <div class="tabbedwidget tab-primary projectTabs">

            <ul>
                <li><a href="#projectdetails"><span class="fa fa-leaf"></span> <?php echo $tpl->__('tabs.projectdetails'); ?></a></li>
                <li><a href="#team"><span class="fa fa-group"></span> <?php echo $tpl->__('tabs.team'); ?></a></li>

                <li><a href="#integrations"> <span class="fa fa-asterisk"></span> <?php echo $tpl->__('tabs.Integrations'); ?></a></li>
                <li><a href="#todosettings"><span class="fa fa-list-ul"></span> <?php echo $tpl->__('tabs.todosettings'); ?></a></li>
                <?php $tpl->dispatchTplEvent('projectTabsList'); ?>
            </ul>

            <div id="projectdetails">
                <?php echo $tpl->displaySubmodule('projects-projectDetails'); ?>
            </div>

            <div id="team">
                <form method="post" action="<?=BASE_URL ?>/projects/showProject/<?php echo $project['id']; ?>#team">
                    <input type="hidden" name="saveUsers" value="1" />


                    <div class="row-fluid">
                    <div class="span12">

                         <div class="form-group">
                             <br /><?=$tpl->__('text.choose_access_for_users'); ?><br />
                             <br />

                            <div class="row">
                                <div class="col-md-12">
                                    <h4 class="widgettitle title-light">
                                        <span class="fa fa-users"></span><?=$tpl->__('headlines.team_member'); ?>
                                    </h4>
                                </div>
                            </div>

                             <div class="row">
                                <?php foreach ($project['assignedUsers'] as $userId => $assignedUser) {?>
                                    <div class="col-md-4">
                                        <div class="userBox">
                                            <input type='checkbox' name='editorId[]' id="user-<?php echo $userId ?>" value='<?php echo $userId ?>'
                                                checked="checked"
                                                />
                                            <div class="commentImage">
                                                <img src="<?= BASE_URL ?>/api/users?profileImage=<?=$userId ?>&v=<?=format($assignedUser['modified'])->timestamp() ?>"/>
                                            </div>
                                            <label for="user-<?php echo $userId ?>" ><?php printf($tpl->__('text.full_name'), $tpl->escape($assignedUser['firstname']), $tpl->escape($assignedUser['lastname'])); ?>
                                                <?php if ($assignedUser['jobTitle'] != '') { ?>
                                                    <small>
                                                        <?= $tpl->escape($assignedUser['jobTitle']) ?>
                                                    </small>
                                                    <br/>
                                                <?php } ?>
                                                <?php if ($assignedUser['status'] == 'i') { ?>
                                                    <small><?= $tpl->__('label.invited') ?></small>
                                                <?php } ?>
                                            </label>
                                            <?php
                                            if (($roles::getRoles()[$assignedUser['role']] == $roles::$admin || $roles::getRoles()[$assignedUser['role']] == $roles::$owner)) { ?>
                                                <input type="text" readonly disabled value="<?php echo $tpl->__("label.roles." . $roles::getRoles()[$assignedUser['role']]) ?>" />
                                            <?php } else { ?>
                                                <select name="userProjectRole-<?php echo $userId ?>">
                                                    <option value="inherit">Inherit</option>
                                                    <option value="<?php echo array_search($roles::$readonly, $roles::getRoles()); ?>"
                                                        <?php if ($assignedUser['projectRole'] == array_search($roles::$readonly, $roles::getRoles())) {
                                                            echo" selected='selected' ";
                                                        }?>
                                                    ><?php echo $tpl->__("label.roles." . $roles::$readonly) ?></option>

                                                    <option value="<?php echo array_search($roles::$commenter, $roles::getRoles()); ?>"
                                                        <?php if ($assignedUser['projectRole'] == array_search($roles::$commenter, $roles::getRoles())) {
                                                            echo" selected='selected' ";
                                                        }?>
                                                    ><?php echo $tpl->__("label.roles." . $roles::$commenter) ?></option>
                                                    <option value="<?php echo array_search($roles::$editor, $roles::getRoles()); ?>"
                                                        <?php if ($assignedUser['projectRole'] == array_search($roles::$editor, $roles::getRoles())) {
                                                            echo" selected='selected' ";
                                                        }?>
                                                    ><?php echo $tpl->__("label.roles." . $roles::$editor) ?></option>
                                                    <option value="<?php echo array_search($roles::$manager, $roles::getRoles()); ?>"
                                                        <?php if ($assignedUser['projectRole'] == array_search($roles::$manager, $roles::getRoles())) {
                                                            echo" selected='selected' ";
                                                        }?>
                                                    ><?php echo $tpl->__("label.roles." . $roles::$manager) ?></option>
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
                                        <span class="fa fa-user-friends "></span><?=$tpl->__('headlines.assign_users_to_project'); ?>
                                    </h4>
                                </div>
                            </div>

                             <div class="row">
                                <?php foreach ($tpl->get('availableUsers') as $row) { ?>
                                    <?php if (!isset($project['assignedUsers'][$row['id']])) { ?>
                                        <div class="col-md-4">
                                            <div class="userBox">
                                                <input type='checkbox' name='editorId[]' id="user-<?php echo $row['id'] ?>" value='<?php echo $row['id'] ?>' />

                                                <div class="commentImage">
                                                    <img src="<?= BASE_URL ?>/api/users?profileImage=<?=$row['id'] ?>&v=<?=format($row['modified'])->timestamp()?>"/>
                                                </div>
                                                <label for="user-<?php echo $row['id'] ?>" ><?php printf($tpl->__('text.full_name'), $tpl->escape($row['firstname']), $tpl->escape($row['lastname'])); ?></label>
                                                <?php if ($roles::getRoles()[$row['role']] == $roles::$admin || $roles::getRoles()[$row['role']] == $roles::$owner) { ?>
                                                    <input type="text" readonly disabled value="<?php echo $tpl->__("label.roles." . $roles::getRoles()[$row['role']]) ?>" />
                                                <?php } else { ?>
                                                    <select name="userProjectRole-<?php echo $row['id'] ?>">
                                                        <option value="inherit">Inherit</option>
                                                        <option value="<?php echo array_search($roles::$readonly, $roles::getRoles()); ?>"
                                                        <?php if (isset($project['assignedUsers'][$row['id']]) && $project['assignedUsers'][$row['id']] == array_search($roles::$readonly, $roles::getRoles())) {
                                                            echo" selected='selected' ";
                                                        }?>
                                                            ><?php echo $tpl->__("label.roles." . $roles::$readonly) ?></option>

                                                        <option value="<?php echo array_search($roles::$commenter, $roles::getRoles()); ?>"
                                                            <?php if (isset($project['assignedUsers'][$row['id']]) && $project['assignedUsers'][$row['id']] == array_search($roles::$commenter, $roles::getRoles())) {
                                                                echo" selected='selected' ";
                                                            }?>
                                                        ><?php echo $tpl->__("label.roles." . $roles::$commenter) ?></option>
                                                        <option value="<?php echo array_search($roles::$editor, $roles::getRoles()); ?>"
                                                            <?php if (isset($project['assignedUsers'][$row['id']]) && $project['assignedUsers'][$row['id']] == array_search($roles::$editor, $roles::getRoles())) {
                                                                echo" selected='selected' ";
                                                            }?>
                                                        ><?php echo $tpl->__("label.roles." . $roles::$editor) ?></option>
                                                        <option value="<?php echo array_search($roles::$manager, $roles::getRoles()); ?>"
                                                            <?php if (isset($project['assignedUsers'][$row['id']]) && $project['assignedUsers'][$row['id']] == array_search($roles::$manager, $roles::getRoles())) {
                                                                echo" selected='selected' ";
                                                            }?>
                                                        ><?php echo $tpl->__("label.roles." . $roles::$manager) ?></option>
                                                    </select>
                                                <?php } ?>
                                                <div class="clearall"></div>
                                            </div>
                                        </div>




                                    <?php }
                                } ?>
                                <?php if ($login::userIsAtLeast($roles::$manager)) { ?>
                                    <div class="col-md-4">

                                        <div class="userBox">
                                            <a class="userEditModal" href="<?=BASE_URL?>/users/newUser?preSelectProjectId=<?=$project['id'] ?>" style="font-size:var(--font-size-l); line-height:61px"><span class="fa fa-user-plus"></span> <?=$tpl->__('links.create_user'); ?></a>
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
                    <input type="submit" name="saveUsers" id="save" class="button" value="<?php echo $tpl->__('buttons.save'); ?>" class="button" />

                </form>

            </div>

            <div id="integrations">

                <h4 class="widgettitle title-light"><span class="fa fa-leaf"></span>Mattermost</h4>
                <div class="row">
                    <div class="col-md-3">
                        <img src="<?=BASE_URL ?>/dist/images/mattermost-logoHorizontal.png" width="200" />
                    </div>
                    <div class="col-md-5">
                        <?=$tpl->__('text.mattermost_instructions'); ?>
                    </div>
                    <div class="col-md-4">
                        <strong><?=$tpl->__('label.webhook_url'); ?></strong><br />
                        <form action="<?=BASE_URL ?>/projects/showProject/<?php echo $project['id']; ?>#integrations" method="post">
                            <input type="text" name="mattermostWebhookURL" id="mattermostWebhookURL" value="<?php echo $tpl->get("mattermostWebhookURL"); ?>"/>
                            <br />
                            <input type="submit" value="<?=$tpl->__('buttons.save'); ?>" name="mattermostSave" />
                        </form>
                    </div>
                </div>
                <br />
                <h4 class="widgettitle title-light"><span class="fa fa-leaf"></span>Slack</h4>
                <div class="row">
                    <div class="col-md-3">
                        <img src="https://cdn.cdnlogo.com/logos/s/52/slack.svg" width="200"/>
                    </div>

                    <div class="col-md-5">
                        <?=$tpl->__('text.slack_instructions'); ?>
                    </div>
                    <div class="col-md-4">
                        <strong><?=$tpl->__('label.webhook_url'); ?></strong><br />
                        <form action="<?=BASE_URL ?>/projects/showProject/<?php echo $project['id']; ?>#integrations" method="post">
                            <input type="text" name="slackWebhookURL" id="slackWebhookURL" value="<?php echo $tpl->get("slackWebhookURL"); ?>"/>
                            <br />
                            <input type="submit" value="<?=$tpl->__('buttons.save'); ?>" name="slackSave" />
                        </form>
                    </div>
                </div>

                <h4 class="widgettitle title-light"><span class="fa fa-leaf"></span>Zulip</h4>
                <div class="row">
                    <div class="col-md-3">
                        <img src="<?=BASE_URL ?>/dist/images/zulip-org-logo.png" width="200"/>
                    </div>

                    <div class="col-md-5">
                        <?=$tpl->__('text.zulip_instructions'); ?>
                    </div>
                    <div class="col-md-4">

                        <form action="<?=BASE_URL ?>/projects/showProject/<?php echo $project['id']; ?>#integrations" method="post">
                            <strong><?=$tpl->__('label.base_url'); ?></strong><br />
                            <input type="text" name="zulipURL" id="zulipURL" placeholder="<?=$tpl->__('input.placeholders.zulip_url'); ?>" value="<?php echo $tpl->get("zulipHook")['zulipURL']; ?>"/>
                            <br />
                            <strong><?=$tpl->__('label.bot_email'); ?></strong><br />
                            <input type="text" name="zulipEmail" id="zulipEmail" placeholder="" value="<?php echo $tpl->get("zulipHook")['zulipEmail']; ?>"/>
                            <br />
                            <strong><?=$tpl->__('label.botkey'); ?></strong><br />
                            <input type="text" name="zulipBotKey" id="zulipBotKey" placeholder="" value="<?php echo $tpl->get("zulipHook")['zulipBotKey']; ?>"/>
                            <br />
                            <strong><?=$tpl->__('label.stream'); ?></strong><br />
                            <input type="text" name="zulipStream" id="zulipStream" placeholder="" value="<?php echo $tpl->get("zulipHook")['zulipStream']; ?>"/>
                            <br />
                            <strong><?=$tpl->__('label.topic'); ?></strong><br />
                            <input type="text" name="zulipTopic" id="zulipTopic" placeholder="" value="<?php echo $tpl->get("zulipHook")['zulipTopic']; ?>"/>
                            <br />
                            <input type="submit" value="<?=$tpl->__('buttons.save'); ?>" name="zulipSave" />
                        </form>
                    </div>
                </div>

                <?php // Slack webhook?>
                <h4 class='widgettitle title-light'><span class='fa fa-leaf'></span>Discord</h4>
                <div class='row'>
                    <div class='col-md-3'>
                        <img src='<?= BASE_URL ?>/dist/images/discord-logo.png' width='200'/>
                    </div>

                    <div class='col-md-5'>
                      <?= $tpl->__('text.discord_instructions'); ?>
                    </div>
                    <div class="col-md-4">
                        <strong><?= $tpl->__('label.webhook_url'); ?></strong><br/>
                        <form action="<?= BASE_URL ?>/projects/showProject/<?php echo $project['id']; ?>#integrations" method="post">
                            <?php for ($i = 1; 3 >= $i; $i++) : ?>
                            <input type="text" name="discordWebhookURL<?=$i; ?>" id="discordWebhookURL<?=$i; ?>" placeholder="<?= $tpl->__('input.placeholders.discord_url'); ?>" value="<?php echo $tpl->get('discordWebhookURL' . $i); ?>"/><br/>
                            <?php endfor; ?>
                            <input type="submit" value="<?= $tpl->__('buttons.save'); ?>" name="discordSave"/>
                        </form>
                    </div>
                </div>

            </div>

            <div id="todosettings">
                <form action="<?=BASE_URL ?>/projects/showProject/<?php echo $project['id']; ?>#todosettings" method="post">
                    <ul class="sortableTicketList" id="todoStatusList">
                        <?php foreach ($tpl->get('todoStatus') as $key => $ticketStatus) { ?>
                            <li>
                                <div class="ticketBox">

                                    <div class="row statusList" id="todostatus-<?=$key ?>">

                                        <input type="hidden" name="labelKeys[]" id="labelKey-<?=$key?>" class='labelKey' value="<?=$key?>"/>
                                        <div class="sortHandle">
                                            <br />
                                            <span class="fa fa-sort"></span>
                                        </div>
                                        <div class="col-md-1">
                                            <label><?=$tpl->__("label.sortindex") ?></label>
                                            <input type="text" name="labelSort-<?=$key?>" class="sorter" id="labelSort-<?=$key ?>" value="<?=$tpl->escape($ticketStatus['sortKey']);?>" style="width:50px;"/>
                                        </div>
                                        <div class="col-md-2">

                                            <label><?=$tpl->__("label.label") ?></label>
                                            <input type="text" name="label-<?=$key?>" <?=$key==-1?'readonly':''?> id="label-<?=$key?>" value="<?=$tpl->escape($ticketStatus['name']);?>" />

                                        </div>
                                        <div class="col-md-2">
                                            <label><?=$tpl->__("label.color") ?></label>
                                            <select name="labelClass-<?=$key?>" id="labelClass-<?=$key ?>" class="colorChosen">
                                                <option value="label-purple" class="label-purple" <?=$ticketStatus['class'] == 'label-purple' ? 'selected="selected"' : ""; ?>><span class="label-purple"><?=$tpl->__('label.purple'); ?></span></option>
                                                <option value="label-pink" class="label-pink" <?=$ticketStatus['class'] == 'label-pink' ? 'selected="selected"' : ""; ?>><span class="label-pink"><?=$tpl->__('label.pink'); ?></span></option>
                                                <option value="label-darker-blue" class="label-darker-blue" <?=$ticketStatus['class'] == 'label-darker-blue' ? 'selected="selected"' : ""; ?>><span class="label-darker-blue"><?=$tpl->__('label.darker-blue'); ?></span></option>
                                                <option value="label-info" class="label-info" <?=$ticketStatus['class'] == 'label-info' ? 'selected="selected"' : ""; ?>><span class="label-info"><?=$tpl->__('label.dark-blue'); ?></span></option>
                                                <option value="label-blue" class="label-blue"  <?=$ticketStatus['class'] == 'label-blue' ? 'selected="selected"' : ""; ?>><span class="label-blue"><?=$tpl->__('label.blue'); ?></span></option>
                                                <option value="label-dark-green" class="label-dark-green" <?=$ticketStatus['class'] == 'label-dark-green' ? 'selected="selected"' : ""; ?>><span class="label-dark-green"><?=$tpl->__('label.dark-green'); ?></span></option>
                                                <option value="label-success" class="label-success" <?=$ticketStatus['class'] == 'label-success' ? 'selected="selected"' : ""; ?>><span class="label-success"><?=$tpl->__('label.green'); ?></span></option>
                                                <option value="label-warning" class="label-warning" <?=$ticketStatus['class'] == 'label-warning' ? 'selected="selected"' : ""; ?>><span class="label-warning"><?=$tpl->__('label.yellow'); ?></span></option>
                                                <option value="label-brown" class="label-brown" <?=$ticketStatus['class'] == 'label-brown' ? 'selected="selected"' : ""; ?>><span class="label-brown"><?=$tpl->__('label.brown'); ?></span></option>
                                                <option value="label-danger" class="label-danger" <?=$ticketStatus['class'] == 'label-danger' ? 'selected="selected"' : ""; ?>><span class="label-danger"><?=$tpl->__('label.dark-red'); ?></span></option>
                                                <option value="label-important" class="label-important" <?=$ticketStatus['class'] == 'label-important' ? 'selected="selected"' : ""; ?>><span class="label-important"><?=$tpl->__('label.red'); ?></span></option>
                                                <option value="label-default" class="label-default" <?=$ticketStatus['class'] == 'label-default' ? 'selected="selected"' : ""; ?>><span class="label-default"><?=$tpl->__('label.grey'); ?></span></option>



                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label><?=$tpl->__("label.reportType") ?></label>
                                            <select name="labelType-<?=$key?>" id="labelType-<?=$key ?>">
                                                <option value="NEW" <?=($ticketStatus['statusType'] == 'NEW') ? 'selected="selected"' : ""; ?>><?=$tpl->__('status.new'); ?></option>
                                                <option value="INPROGRESS" <?=($ticketStatus['statusType'] == 'INPROGRESS') ? 'selected="selected"' : ""; ?>><?=$tpl->__('status.in_progress'); ?></option>
                                                <option value="DONE" <?=($ticketStatus['statusType'] == 'DONE') ? 'selected="selected"' : ""; ?>><?=$tpl->__('status.done'); ?></option>
                                                <option value="NONE" <?=($ticketStatus['statusType'] == 'NONE') ? 'selected="selected"' : ""; ?>><?=$tpl->__('status.dont_report'); ?></option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label for=""><?=$tpl->__('label.showInKanban'); ?></label>
                                            <input type="checkbox" name="labelKanbanCol-<?=$key?>" id="labelKanbanCol-<?=$key?>" <?= $ticketStatus['kanbanCol'] ? 'checked="checked"' : ""; ?>/>
                                        </div>
                                        <div class="remove">
                                            <br />
                                            <?php if($key != -1){ ?>
                                                <a href="javascript:void(0);" onclick="leantime.projectsController.removeStatus(<?=$key?>)" class="delete"><span class="fa fa-trash"></span></a>
                                            <?php } ?>
                                        </div>
                                    </div>

                                    <?php if ($key == -1) { ?>
                                        <em>* the archive status is protected cannot be renamed or removed.</em>
                                    <?php } ?>
                                </div>
                            </li>

                        <?php } ?>
                    </ul>

                    <a href="javascript:void(0);" onclick="leantime.projectsController.addToDoStatus();" class="quickAddLink" style="text-align:left;"><?=$tpl->__('links.add_status'); ?></a>
                    <br />
                    <input type="submit" value="<?=$tpl->__('buttons.save')?>" name="submitSettings" class="btn btn-primary"/>
                </form>
            </div>

            <?php $tpl->dispatchTplEvent('projectTabsContent'); ?>
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
            <label><?=$tpl->__("label.sortindex") ?></label>
            <input type="text" name="labelSort-XXNEWKEYXX" class="sorter" id="labelSort-XXNEWKEYXX" value="" style="width:50px;"/>
        </div>
        <div class="col-md-2">
            <label><?=$tpl->__("label.label") ?></label>
            <input type="text" name="label-XXNEWKEYXX" id="label-XXNEWKEYXX" value="" />

        </div>
        <div class="col-md-2">
            <label><?=$tpl->__("label.color") ?></label>
            <select name="labelClass-XXNEWKEYXX" id="labelClass-XXNEWKEYXX" class="colorChosen">
                <option value="label-blue" class="label-blue"><span class="label-blue"><?=$tpl->__('label.blue'); ?></span></option>
                <option value="label-info" class="label-info"><span class="label-info"><?=$tpl->__('label.dark-blue'); ?></span></option>
                <option value="label-darker-blue" class="label-darker-blue"><span class="label-darker-blue"><?=$tpl->__('label.darker-blue'); ?></span></option>
                <option value="label-warning" class="label-warning"><span class="label-warning"><?=$tpl->__('label.yellow'); ?></span></option>
                <option value="label-success" class="label-success"><span class="label-success"><?=$tpl->__('label.green'); ?></span></option>
                <option value="label-dark-green" class="label-dark-green"><span class="label-dark-green"><?=$tpl->__('label.dark-green'); ?></span></option>
                <option value="label-important" class="label-important"><span class="label-important"><?=$tpl->__('label.red'); ?></span></option>
                <option value="label-danger" class="label-danger"><span class="label-danger"><?=$tpl->__('label.dark-red'); ?></span></option>
                <option value="label-pink" class="label-pink"><span class="label-pink"><?=$tpl->__('label.pink'); ?></span></option>
                <option value="label-purple" class="label-purple"><span class="label-purple"><?=$tpl->__('label.purple'); ?></span></option>
                <option value="label-brown" class="label-brown"><span class="label-brown"><?=$tpl->__('label.brown'); ?></span></option>
                <option value="label-default" class="label-default"><span class="label-default"><?=$tpl->__('label.grey'); ?></span></option>
            </select>
        </div>
        <div class="col-md-2">
            <label><?=$tpl->__("label.reportType") ?></label>
            <select name="labelType-XXNEWKEYXX" id="labelType-XXNEWKEYXX">
                <option value="NEW"><?=$tpl->__('status.new'); ?></option>
                <option value="INPROGRESS"><?=$tpl->__('status.in_progress'); ?></option>
                <option value="DONE"><?=$tpl->__('status.done'); ?></option>
                <option value="NONE"><?=$tpl->__('status.dont_report'); ?></option>
            </select>
        </div>
        <div class="col-md-2">
            <label for=""><?=$tpl->__('label.showInKanban'); ?></label>
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
        jQuery("#projectdetails select").chosen();

        <?php if (isset($_GET['integrationSuccess'])) {?>
            window.history.pushState({},document.title, '<?=BASE_URL ?>/projects/showProject/<?php echo (int)$project['id']; ?>');
        <?php } ?>

        jQuery(".dates").datepicker(
            {
                dateFormat: leantime.dateHelper.getFormatFromSettings("dateformat", "jquery"),
                dayNames: leantime.i18n.__("language.dayNames").split(","),
                dayNamesMin:  leantime.i18n.__("language.dayNamesMin").split(","),
                dayNamesShort: leantime.i18n.__("language.dayNamesShort").split(","),
                monthNames: leantime.i18n.__("language.monthNames").split(","),
                currentText: leantime.i18n.__("language.currentText"),
                closeText: leantime.i18n.__("language.closeText"),
                buttonText: leantime.i18n.__("language.buttonText"),
                isRTL: leantime.i18n.__("language.isRTL") === "true" ? 1 : 0,
                nextText: leantime.i18n.__("language.nextText"),
                prevText: leantime.i18n.__("language.prevText"),
                weekHeader: leantime.i18n.__("language.weekHeader"),
                firstDay: leantime.i18n.__("language.firstDayOfWeek"),
            }
        );

        leantime.projectsController.initProjectTabs();
        leantime.projectsController.initDuplicateProjectModal();
        leantime.projectsController.initTodoStatusSortable("#todoStatusList");
        leantime.projectsController.initSelectFields();
        leantime.usersController.initUserEditModal();

        leantime.editorController.initComplexEditor();

    });

</script>
