<?php
    defined('RESTRICTED') or die('Restricted access');
    $project = $this->get('project');
    $bookedHours = $this->get('bookedHours');
    $helper = $this->get('helper');
    $state = $this->get('state');
?>

<div class="pageheader">
    <div class="pull-right padding-top">
        <a href="<?=BASE_URL ?>/projects/showAll" class="backBtn"><i class="far fa-arrow-alt-circle-left"></i> <?php echo $this->__('links.go_back') ?></a>
    </div>

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
                    <li><a href="#projectdetails"><?php echo $this->__('tabs.projectdetails'); ?></a></li>
                    <li><a href="#integrations"><?php echo $this->__('tabs.Integrations'); ?></a></li>
                    <li><a href="#files"><?php echo sprintf($this->__('tabs.files_with_count'), $this->get('numFiles')); ?></a></li>
                    <li><a href="#comment"><?php echo sprintf($this->__('tabs.discussion_with_count'), $this->get('numComments')); ?></a></li>
                </ul>

                <div id="projectdetails">
                    <?php echo $this->displaySubmodule('projects-projectDetails'); ?>
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

                                                            <?php  if ($login::userIsAtLeast("developer")) { ?>
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
                                                        <span class="filename"><?php echo $file['realName'] ?></span>

                                                      </a>

                                                   </li>
                                <?php endforeach; ?>
                                           <br class="clearall" />
                        </ul>

                    </div><!--mediamgr_content-->
                    <div style='clear:both'>&nbsp;</div>
                    
                </div><!-- end files -->
                
                <div id="comment">

                    <form method="post" action="<?=BASE_URL ?>/projects/showProject/<?php echo $project['id']; ?>#comment" class="ticketModal">
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
        leantime.projectsController.initProjectsEditor();
        leantime.projectsController.initDuplicateProjectModal();

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