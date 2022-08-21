<?php
    defined('RESTRICTED') or die('Restricted access');
    $project = $this->get('project');
    $bookedHours = $this->get('bookedHours');
    $state = $this->get('state');
?>

<div class="pageheader">
    <div class="pageicon"><span class="fa fa-suitcase"></span></div>
    <div class="pagetitle">
        <h5><?php echo $this->__('label.administration') ?></h5>
        <h1><?php echo sprintf($this->__('headline.project'),$this->escape($project['name'])); ?>
        </h1>
    </div>
</div><!--pageheader-->
        
<div class="maincontent">
    <div class="maincontentinner">

        <?php echo $this->displayNotification() ?>

        <div class="row">
            <div class="col-md-12">
                <div class="pull-right">
                    <a href="<?=BASE_URL?>/projects/duplicateProject/<?=$project['id']?>" class="duplicateProjectModal btn btn-default"><?=$this->__("links.duplicate_project") ?></a>

                </div>
            </div>
        </div>

        <div class="tabbedwidget tab-primary projectTabs">

            <ul>
                <li><a href="#projectdetails"><span class="iconfa iconfa-leaf"></span> <?php echo $this->__('tabs.projectdetails'); ?></a></li>
                <li><a href="#team"><span class="iconfa iconfa-group"></span> <?php echo $this->__('tabs.team'); ?></a></li>

                <li><a href="#integrations"> <span class="iconfa iconfa-asterisk"></span> <?php echo $this->__('tabs.Integrations'); ?></a></li>
                <li><a href="#files"><span class="fa fa-file"></span> <?php echo sprintf($this->__('tabs.files_with_count'), $this->get('numFiles')); ?></a></li>
                <li><a href="#comment"><span class="fa fa-comments"></span> <?php echo sprintf($this->__('tabs.discussion_with_count'), $this->get('numComments')); ?></a></li>
                <li><a href="#todosettings"><span class="fa fa-list-ul"></span> <?php echo $this->__('tabs.todosettings'); ?></a></li>
            </ul>

            <div id="projectdetails">
                <?php echo $this->displaySubmodule('projects-projectDetails'); ?>
            </div>

            <div id="team">
                <form method="post" action="<?=BASE_URL ?>/projects/showProject/<?php echo $project['id']; ?>#team">
                    <input type="hidden" name="saveUsers" value="1" />


                    <div class="row-fluid">
                    <div class="span12">

                         <div class="form-group">
                             <br />
                            <?php echo $this->__('text.choose_access_for_users'); ?><br />
                             <br />

                            <div class="row">
                                <div class="col-md-12">
                                    <h4 class="widgettitle title-light">
                                        <span class="fa fa-users"></span><?=$this->__('headlines.team_member'); ?>
                                    </h4>
                                </div>
                            </div>

                             <div class="row">
                                <?php foreach($project['assignedUsers'] as $userId => $assignedUser){ ?>

                                    <div class="col-md-4">
                                        <div class="userBox">
                                            <input type='checkbox' name='editorId[]' id="user-<?php echo $userId ?>" value='<?php echo $userId ?>'
                                                checked="checked"
                                                />
                                            <div class="commentImage">
                                                <img src="<?= BASE_URL ?>/api/users?profileImage=<?= $assignedUser['profileId'] ?>"/>
                                            </div>
                                            <label for="user-<?php echo $userId ?>" ><?php printf( $this->__('text.full_name'), $this->escape($assignedUser['firstname']), $this->escape($assignedUser['lastname'])); ?></label>
                                            <?php
                                            if(($roles::getRoles()[$assignedUser['role']] == $roles::$admin || $roles::getRoles()[$assignedUser['role']] == $roles::$owner)) { ?>
                                                <input type="text" readonly disabled value="<?php echo $this->__("label.roles.".$roles::getRoles()[$assignedUser['role']]) ?>" />
                                            <?php }else{ ?>

                                                <select name="userProjectRole-<?php echo $userId ?>">
                                                    <option value="inherit">Inherit</option>
                                                    <option value="<?php echo array_search($roles::$readonly, $roles::getRoles()); ?>"
                                                        <?php if($assignedUser['projectRole'] == array_search($roles::$readonly, $roles::getRoles())) {
                                                            echo" selected='selected' ";
                                                        }?>
                                                    ><?php echo $this->__("label.roles.".$roles::$readonly) ?></option>

                                                    <option value="<?php echo array_search($roles::$commenter, $roles::getRoles()); ?>"
                                                        <?php if($assignedUser['projectRole'] == array_search($roles::$commenter, $roles::getRoles())) {
                                                            echo" selected='selected' ";
                                                        }?>
                                                    ><?php echo $this->__("label.roles.".$roles::$commenter) ?></option>
                                                    <option value="<?php echo array_search($roles::$editor, $roles::getRoles()); ?>"
                                                        <?php if($assignedUser['projectRole'] == array_search($roles::$editor, $roles::getRoles())) {
                                                            echo" selected='selected' ";
                                                        }?>
                                                    ><?php echo $this->__("label.roles.".$roles::$editor) ?></option>
                                                    <option value="<?php echo array_search($roles::$manager, $roles::getRoles()); ?>"
                                                        <?php if($assignedUser['projectRole'] == array_search($roles::$manager, $roles::getRoles())) {
                                                            echo" selected='selected' ";
                                                        }?>
                                                    ><?php echo $this->__("label.roles.".$roles::$manager) ?></option>
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
                                        <span class="fa fa-user-plus"></span><?=$this->__('headlines.add_more_users'); ?>
                                    </h4>
                                </div>
                            </div>

                             <div class="row">
                                <?php foreach($this->get('availableUsers') as $row){ ?>
                                    <?php if(!isset($project['assignedUsers'][$row['id']])) { ?>

                                        <div class="col-md-4">
                                            <div class="userBox">
                                                <input type='checkbox' name='editorId[]' id="user-<?php echo $row['id'] ?>" value='<?php echo $row['id'] ?>' />

                                                <div class="commentImage">
                                                    <img src="<?= BASE_URL ?>/api/users?profileImage=<?= $row['profileId'] ?>"/>
                                                </div>
                                                <label for="user-<?php echo $row['id'] ?>" ><?php printf( $this->__('text.full_name'), $this->escape($row['firstname']), $this->escape($row['lastname'])); ?></label>
                                                <?php if($roles::getRoles()[$row['role']] == $roles::$admin || $roles::getRoles()[$row['role']] == $roles::$owner) { ?>
                                                    <input type="text" readonly disabled value="<?php echo $this->__("label.roles.".$roles::getRoles()[$row['role']]) ?>" />
                                                <?php }else{ ?>

                                                    <select name="userProjectRole-<?php echo $row['id'] ?>">
                                                        <option value="inherit">Inherit</option>
                                                        <option value="<?php echo array_search($roles::$readonly, $roles::getRoles()); ?>"
                                                        <?php if(isset($project['assignedUsers'][$row['id']]) && $project['assignedUsers'][$row['id']] == array_search($roles::$readonly, $roles::getRoles())) {
                                                            echo" selected='selected' ";
                                                        }?>
                                                            ><?php echo $this->__("label.roles.".$roles::$readonly) ?></option>

                                                        <option value="<?php echo array_search($roles::$commenter, $roles::getRoles()); ?>"
                                                            <?php if(isset($project['assignedUsers'][$row['id']]) && $project['assignedUsers'][$row['id']] == array_search($roles::$commenter, $roles::getRoles())) {
                                                                echo" selected='selected' ";
                                                            }?>
                                                        ><?php echo $this->__("label.roles.".$roles::$commenter) ?></option>
                                                        <option value="<?php echo array_search($roles::$editor, $roles::getRoles()); ?>"
                                                            <?php if(isset($project['assignedUsers'][$row['id']]) && $project['assignedUsers'][$row['id']] == array_search($roles::$editor, $roles::getRoles())) {
                                                                echo" selected='selected' ";
                                                            }?>
                                                        ><?php echo $this->__("label.roles.".$roles::$editor) ?></option>
                                                        <option value="<?php echo array_search($roles::$manager, $roles::getRoles()); ?>"
                                                            <?php if(isset($project['assignedUsers'][$row['id']]) && $project['assignedUsers'][$row['id']] == array_search($roles::$manager, $roles::getRoles())) {
                                                                echo" selected='selected' ";
                                                            }?>
                                                        ><?php echo $this->__("label.roles.".$roles::$manager) ?></option>
                                                    </select>
                                                <?php } ?>
                                                <div class="clearall"></div>
                                            </div>
                                        </div>


                                <?php }
                                } ?>
                            </div>
                        </div>


                    </div>
                </div>
                    <br/>
                    <input type="submit" name="saveUsers" id="save" class="button" value="<?php echo $this->__('buttons.save'); ?>" class="button" />

                </form>

            </div>

            <div id="files">

                <div class="mediamgr_category">
                            <form action='#files' method='POST' enctype="multipart/form-data" id="fileForm">

                            <div class="par f-left" style="margin-right: 15px;">

                                 <div class='fileupload fileupload-new' data-provides='fileupload'>
                                        <input type="hidden" />
                                    <div class="input-append">
                                        <div class="uneditable-input span3">
                                            <i class="iconfa-file fileupload-exists"></i><span class="fileupload-preview"></span>
                                        </div>
                                        <span class="btn btn-file">
                                            <span class="fileupload-new"><?=$this->__('label.select_file'); ?></span>
                                            <span class='fileupload-exists'><?=$this->__('label.change'); ?></span>
                                            <input type='file' name='file' />
                                        </span>
                                        <a href='#' class='btn fileupload-exists' data-dismiss='fileupload'><?=$this->__('buttons.remove'); ?></a>
                                    </div>
                                  </div>
                               </div>

                               <input type="submit" name="upload" class="button" value="<?=$this->__('buttons.upload'); ?>" />

                            </form>
                </div>


                <div class="mediamgr_content">

                    <ul id='medialist' class='listfile'>
                            <?php foreach($this->get('files') as $file): ?>
                                            <li class="<?php echo $file['moduleId'] ?>">
                                                <div class="inlineDropDownContainer" style="float:right;">

                                                    <a href="javascript:void(0);" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                                                        <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                                    </a>
                                                    <ul class="dropdown-menu">
                                                        <li class="nav-header"><?php echo $this->__("subtitles.file"); ?></li>
                                                        <li><a href="<?=BASE_URL ?>/download.php?module=<?php echo $file['module'] ?>&encName=<?php echo $file['encName'] ?>&ext=<?php echo $file['extension'] ?>&realName=<?php echo $file['realName'] ?>"><?php echo $this->__("links.download"); ?></a></li>

                                                        <?php if ($login::userIsAtLeast($roles::$manager)) { ?>
                                                            <li><a href="<?=BASE_URL ?>/projects/showProject/<?php echo $project['id']; ?>?delFile=<?php echo $file['id'] ?>" class="delete"><i class="fa fa-trash"></i> <?php echo $this->__("links.delete"); ?></a></li>
                                                        <?php  } ?>

                                                    </ul>
                                                </div>
                                                  <a class="cboxElement" href="<?=BASE_URL ?>/download.php?module=<?php echo $file['module'] ?>&encName=<?php echo $file['encName'] ?>&ext=<?php echo $file['extension'] ?>&realName=<?php echo $file['realName'] ?>">
                                                    <?php if (in_array(strtolower($file['extension']), $this->get('imgExtensions'))) :  ?>
                                                        <img style='max-height: 50px; max-width: 70px;' src="<?=BASE_URL ?>/download.php?module=<?php echo $file['module'] ?>&encName=<?php echo $file['encName'] ?>&ext=<?php echo $file['extension'] ?>&realName=<?php echo $file['realName'] ?>" alt="" />
                                                        <?php else: ?>
                                                        <img style='max-height: 50px; max-width: 70px;' src='<?=BASE_URL ?>/images/thumbs/doc.png' />
                                                        <?php endif; ?>
                                                    <span class="filename"><?php $this->e($file['realName']) ?></span>

                                                  </a>

                                               </li>
                            <?php endforeach; ?>
                                       <br class="clearall" />
                    </ul>

                </div><!--mediamgr_content-->
                <div style='clear:both'>&nbsp;</div>

            </div><!-- end files -->

            <div id="comment">

                <form method="post" action="<?=BASE_URL ?>/projects/showProject/<?php echo $project['id']; ?>#comment">
                    <input type="hidden" name="comment" value="1" />
                    <?php
                    $this->assign('formUrl', BASE_URL."/projects/showProject/".$project['id']."");
                    $this->displaySubmodule('comments-generalComment') ;
                    ?>
                </form>

            </div>

            <div id="integrations">

                <h4 class="widgettitle title-light"><span class="iconfa iconfa-leaf"></span>Mattermost</h4>
                <div class="row">
                    <div class="col-md-3">
                        <img src="<?=BASE_URL ?>/images/mattermost-logoHorizontal.png" width="200" />
                    </div>
                    <div class="col-md-5">
                        <?=$this->__('text.mattermost_instructions'); ?>
                    </div>
                    <div class="col-md-4">
                        <strong><?=$this->__('label.webhook_url'); ?></strong><br />
                        <form action="<?=BASE_URL ?>/projects/showProject/<?php echo $project['id']; ?>#integrations" method="post">
                            <input type="text" name="mattermostWebhookURL" id="mattermostWebhookURL" value="<?php echo $this->get("mattermostWebhookURL"); ?>"/>
                            <br />
                            <input type="submit" value="<?=$this->__('buttons.save'); ?>" name="mattermostSave" />
                        </form>
                    </div>
                </div>
                <br />
                <h4 class="widgettitle title-light"><span class="iconfa iconfa-leaf"></span>Slack</h4>
                <div class="row">
                    <div class="col-md-3">
                        <img src="https://cdn.brandfolder.io/5H442O3W/as/pl546j-7le8zk-5guop3/Slack_RGB.png " width="200"/>
                    </div>

                    <div class="col-md-5">
                        <?=$this->__('text.slack_instructions'); ?>
                    </div>
                    <div class="col-md-4">
                        <strong><?=$this->__('label.webhook_url'); ?></strong><br />
                        <form action="<?=BASE_URL ?>/projects/showProject/<?php echo $project['id']; ?>#integrations" method="post">
                            <input type="text" name="slackWebhookURL" id="slackWebhookURL" value="<?php echo $this->get("slackWebhookURL"); ?>"/>
                            <br />
                            <input type="submit" value="<?=$this->__('buttons.save'); ?>" name="slackSave" />
                        </form>
                    </div>
                </div>

                <h4 class="widgettitle title-light"><span class="iconfa iconfa-leaf"></span>Zulip</h4>
                <div class="row">
                    <div class="col-md-3">
                        <img src="<?=BASE_URL ?>/images/zulip-org-logo.png" width="200"/>
                    </div>

                    <div class="col-md-5">
                        <?=$this->__('text.zulip_instructions'); ?>
                    </div>
                    <div class="col-md-4">

                        <form action="<?=BASE_URL ?>/projects/showProject/<?php echo $project['id']; ?>#integrations" method="post">
                            <strong><?=$this->__('label.base_url'); ?></strong><br />
                            <input type="text" name="zulipURL" id="zulipURL" placeholder="<?=$this->__('input.placeholders.zulip_url'); ?>" value="<?php echo $this->get("zulipHook")['zulipURL']; ?>"/>
                            <br />
                            <strong><?=$this->__('label.bot_email'); ?></strong><br />
                            <input type="text" name="zulipEmail" id="zulipEmail" placeholder="" value="<?php echo $this->get("zulipHook")['zulipEmail']; ?>"/>
                            <br />
                            <strong><?=$this->__('label.botkey'); ?></strong><br />
                            <input type="text" name="zulipBotKey" id="zulipBotKey" placeholder="" value="<?php echo $this->get("zulipHook")['zulipBotKey']; ?>"/>
                            <br />
                            <strong><?=$this->__('label.stream'); ?></strong><br />
                            <input type="text" name="zulipStream" id="zulipStream" placeholder="" value="<?php echo $this->get("zulipHook")['zulipStream']; ?>"/>
                            <br />
                            <strong><?=$this->__('label.topic'); ?></strong><br />
                            <input type="text" name="zulipTopic" id="zulipTopic" placeholder="" value="<?php echo $this->get("zulipHook")['zulipTopic']; ?>"/>
                            <br />
                            <input type="submit" value="<?=$this->__('buttons.save'); ?>" name="zulipSave" />
                        </form>
                    </div>
                </div>

                <?php // Slack webhook ?>
                <h4 class='widgettitle title-light'><span class='iconfa iconfa-leaf'></span>Discord</h4>
                <div class='row'>
                    <div class='col-md-3'>
                        <img src='<?= BASE_URL ?>/images/discord-logo.png' width='200'/>
                    </div>

                    <div class='col-md-5'>
                      <?= $this->__('text.discord_instructions'); ?>
                    </div>
                    <div class="col-md-4">
                        <strong><?= $this->__('label.webhook_url'); ?></strong><br/>
                        <form action="<?= BASE_URL ?>/projects/showProject/<?php echo $project['id']; ?>#integrations" method="post">
                            <?php for ($i = 1; 3 >= $i ; $i++): ?>
                            <input type="text" name="discordWebhookURL<?=$i; ?>" id="discordWebhookURL<?=$i; ?>" placeholder="<?= $this->__('input.placeholders.discord_url'); ?>" value="<?php echo $this->get('discordWebhookURL' . $i); ?>"/><br/>
                            <?php endfor; ?>
                            <input type="submit" value="<?= $this->__('buttons.save'); ?>" name="discordSave"/>
                        </form>
                    </div>
                </div>

            </div>

            <div id="todosettings">
                <form action="<?=BASE_URL ?>/projects/showProject/<?php echo $project['id']; ?>#todosettings" method="post">
                    <ul class="sortableTicketList" id="todoStatusList">
                        <?php foreach($this->get('todoStatus') as $key => $ticketStatus) { ?>

                            <li>
                                <div class="ticketBox">
                                    <div class="row statusList" id="todostatus-<?=$key?>">
                                        <input type="hidden" name="labelKeys[]" id="labelKey-<?=$key?>" class='labelKey' value="<?=$key?>"/>
                                        <div class="sortHandle">
                                            <br />
                                            <span class="fa fa-sort"></span>
                                        </div>
                                        <div class="col-md-1">
                                            <label><?=$this->__("label.sortindex") ?></label>
                                            <input type="text" name="labelSort-<?=$key?>" class="sorter" id="labelSort-<?=$key ?>" value="<?=$this->escape($ticketStatus['sortKey']);?>" style="width:30px;"/>
                                        </div>
                                        <div class="col-md-2">
                                            <label><?=$this->__("label.label") ?></label>
                                            <input type="text" name="label-<?=$key?>" id="label-<?=$key?>" value="<?=$this->escape($ticketStatus['name']);?>" />

                                        </div>
                                        <div class="col-md-2">
                                            <label><?=$this->__("label.color") ?></label>
                                            <select name="labelClass-<?=$key?>" id="labelClass-<?=$key ?>" class="colorChosen">
                                                <option value="label-info" class="label-info" <?=$ticketStatus['class']=='label-info'?'selected="selected"':""; ?>><span class="label-info"><?=$this->__('label.blue'); ?></span></option>
                                                <option value="label-warning" class="label-warning" <?=$ticketStatus['class']=='label-warning'?'selected="selected"':""; ?>><span class="label-warning"><?=$this->__('label.yellow'); ?></span></option>
                                                <option value="label-success" class="label-success" <?=$ticketStatus['class']=='label-success'?'selected="selected"':""; ?>><span class="label-success"><?=$this->__('label.green'); ?></span></option>
                                                <option value="label-important" class="label-important" <?=$ticketStatus['class']=='label-important'?'selected="selected"':""; ?>><span class="label-important"><?=$this->__('label.red'); ?></span></option>
                                                <option value="label-default" class="label-default" <?=$ticketStatus['class']=='label-default'?'selected="selected"':""; ?>><span class="label-default"><?=$this->__('label.grey'); ?></span></option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label><?=$this->__("label.reportType") ?></label>
                                            <select name="labelType-<?=$key?>" id="labelType-<?=$key ?>">
                                                <option value="NEW" <?=($ticketStatus['statusType']=='NEW')?'selected="selected"':""; ?>><?=$this->__('status.new'); ?></option>
                                                <option value="INPROGRESS" <?=($ticketStatus['statusType']=='INPROGRESS')?'selected="selected"':""; ?>><?=$this->__('status.in_progress'); ?></option>
                                                <option value="DONE" <?=($ticketStatus['statusType']=='DONE')?'selected="selected"':""; ?>><?=$this->__('status.done'); ?></option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label for=""><?=$this->__('label.showInKanban'); ?></label>
                                            <input type="checkbox" name="labelKanbanCol-<?=$key?>" id="labelKanbanCol-<?=$key?>" <?=($ticketStatus['kanbanCol']==true)?'checked="checked"':""; ?>/>
                                        </div>
                                        <div class="remove">
                                            <br />
                                            <a href="javascript:void()" onclick="leantime.projectsController.removeStatus(<?=$key?>)" class="delete"><span class="fa fa-trash"></span></a>
                                        </div>
                                    </div>
                                </div>
                            </li>

                        <?php } ?>
                    </ul>

                    <a href="javascript:void(0);" onclick="leantime.projectsController.addToDoStatus();" class="quickAddLink" style="text-align:left;"><?=$this->__('links.add_status'); ?></a>
                    <br />
                    <input type="submit" value="<?=$this->__('buttons.save')?>" name="submitSettings" class="btn btn-primary"/>
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
                <option value="label-info" class="label-info"><span class="label-info"><?=$this->__('label.blue'); ?></span></option>
                <option value="label-warning" class="label-warning"><span class="label-warning"><?=$this->__('label.yellow'); ?></span></option>
                <option value="label-success" class="label-success"><span class="label-success"><?=$this->__('label.green'); ?></span></option>
                <option value="label-important" class="label-important"><span class="label-important"><?=$this->__('label.red'); ?></span></option>
                <option value="label-default" class="label-default"><span class="label-default"><?=$this->__('label.grey'); ?></span></option>
            </select>
        </div>
        <div class="col-md-2">
            <label><?=$this->__("label.reportType") ?></label>
            <select name="labelType-XXNEWKEYXX" id="labelType-XXNEWKEYXX">
                <option value="NEW"><?=$this->__('status.new'); ?></option>
                <option value="INPROGRESS"><?=$this->__('status.in_progress'); ?></option>
                <option value="DONE"><?=$this->__('status.done'); ?></option>
            </select>
        </div>
        <div class="col-md-2">
            <label for=""><?=$this->__('label.showInKanban'); ?></label>
            <input type="checkbox" name="labelKanbanCol-XXNEWKEYXX" id="labelKanbanCol-XXNEWKEYXX"/>
        </div>
        <div class="remove">
            <br />
            <a href="javascript:void()" onclick="leantime.projectsController.removeStatus(XXNEWKEYXX)" class="delete"><span class="fa fa-trash"></span></a>
        </div>
    </div>
</div>
</div>

<script type='text/javascript'>

    jQuery(document).ready(function() {
        <?php if(isset($_GET['integrationSuccess'])) {?>
            window.history.pushState({},document.title, '<?=BASE_URL ?>/projects/showProject/<?php echo (int)$project['id']; ?>');
        <?php } ?>

        leantime.projectsController.initProjectTabs();
        leantime.projectsController.initDuplicateProjectModal();
        leantime.projectsController.initTodoStatusSortable("#todoStatusList");
        leantime.projectsController.initSelectFields();

        <?php
        if(isset($_SESSION['tourActive']) === true && $_SESSION['tourActive'] == 1) {
        ?>
            leantime.helperController.showHelperModal("projectSuccess");

        <?php
            $_SESSION['tourActive'] = false;
        }
        ?>
    });

</script>