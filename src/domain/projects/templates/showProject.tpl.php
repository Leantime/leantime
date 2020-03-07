<?php

defined('RESTRICTED') or die('Restricted access');
$project = $this->get('project');
$bookedHours = $this->get('bookedHours');
$helper = $this->get('helper');
$state = $this->get('state');
?>


<script type="text/javascript">
    jQuery(document).ready(function() { 
             toggleCommentBoxes(0);
         
            jQuery('.tabbedwidget').tabs();
            
/*            jQuery('#commentList').pager('div');*/
             
            jQuery("#progressbar").progressbar({
                value: <?php echo $this->get('projectPercentage') ?>
            });
        
            jQuery("#accordion").accordion({
                autoHeight: false,
                navigation: true
            });

            jQuery("#dateFrom, #dateTo").datepicker({
                
                dateFormat: 'dd.mm.yy',
                dayNames: [<?php echo''.$lang['DAYNAMES'].'' ?>],
                dayNamesMin:  [<?php echo''.$lang['DAYNAMES_MIN'].'' ?>],
                monthNames: [<?php echo''.$lang['MONTHS'].'' ?>]
            });


            <?php
            if(isset($_SESSION['tourActive']) === true && $_SESSION['tourActive'] == 1) {     ?>
                leantime.helperController.showHelperModal("projectSuccess");
            <?php } ?>

        } 
    ); 

function toggleCommentBoxes(id){
        
        jQuery('.commentBox').hide('fast',function(){

            jQuery('.commentBox textarea').remove(); 

            jQuery('#comment'+id+'').prepend('<textarea rows="5" cols="50" name="text"></textarea>');
                
        }); 

        jQuery('#comment'+id+'').show('slow');        

        
    }
</script>

<div class="pageheader">

    <div class="pull-right padding-top">
        <a href="<?=BASE_URL ?>/projects/showAll" class="backBtn"><i class="far fa-arrow-alt-circle-left"></i> Go Back</a>
    </div>

    <div class="pageicon"><span class="fa fa-suitcase"></span></div>
            <div class="pagetitle">
                <h5>Administration</h5>
                <h1><?php echo $language->lang_echo('PROJECT') ?> <?php echo $project['name']; ?></h1>
            </div>
</div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">

                <?php echo $this->displayNotification() ?>

                <div class="tabbedwidget tab-primary">

                <ul>
                    <li><a href="#projectdetails"><?php echo $language->lang_echo('PROJECT_DETAILS'); ?></a></li>
                    <li><a href="#integrations">Integrations</a></li>
                    <li><a href="#files"><?php echo $language->lang_echo('FILES'); ?> (<?php echo $this->get('numFiles'); ?>)</a></li>
                    <li><a href="#comment">Discussion (<?php echo $this->get('numComments'); ?>)</a></li>

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
                                                <span class="fileupload-new">Select file</span>
                                                <span class='fileupload-exists'>Change</span>
                                                <input type='file' name='file' />
                                            </span>
                                            <a href='#' class='btn fileupload-exists' data-dismiss='fileupload'>Remove</a>
                                        </div>
                                      </div>
                                   </div>

                                   <input type="submit" name="upload" class="button" value="<?php echo $language->lang_echo('UPLOAD'); ?>" />

                                </form>
                    </div>


                    <div class="mediamgr_content">

                        <ul id='medialist' class='listfile'>
                                <?php foreach($this->get('files') as $file): ?>
                                                <li class="<?php echo $file['moduleId'] ?>">
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
                        $this->displaySubmodule('comments-generalComment') ?>
                    </form>

                </div>
                
                    <div id="integrations">

                        <h4 class="widgettitle title-light"><span class="iconfa iconfa-leaf"></span>Mattermost</h4>
                        <div class="row">
                            <div class="col-md-3">
                                <img src="<?=BASE_URL ?>/images/mattermost-logoHorizontal.png" width="200" />
                            </div>
                            <div class="col-md-5">
                                This integration will post update notifications to the channel of your choice.<br />
                                Follow the instructions <a href="https://docs.mattermost.com/developer/webhooks-incoming.html#simple-incoming-webhook" target="_blank">here to get an Incoming Webhook URL</a> from Mattermost. Then paste the link into the form to the right and click Save.

                            </div>
                            <div class="col-md-4">
                                <strong>Webhook URL</strong><br />
                                <form action="<?=BASE_URL ?>/projects/showProject/<?php echo $project['id']; ?>#integrations" method="post">
                                    <input type="text" name="mattermostWebhookURL" id="mattermostWebhookURL" value="<?php echo $this->get("mattermostWebhookURL"); ?>"/>
                                    <br />
                                    <input type="submit" value="Save" name="mattermostSave" />
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
                                This integration will post update notifications to the channel of your choice.<br />
                                Follow the instructions <a href="https://get.slack.help/hc/en-us/articles/115005265063-Incoming-WebHooks-for-Slack" target="_blank">here to get an Incoming Webhook URL</a> from Slack. Then paste the link into the form to the right and click Save.
                            </div>
                            <div class="col-md-4">
                                <strong>Webhook URL</strong><br />
                                <form action="<?=BASE_URL ?>/projects/showProject/<?php echo $project['id']; ?>#integrations" method="post">
                                    <input type="text" name="slackWebhookURL" id="slackWebhookURL" value="<?php echo $this->get("slackWebhookURL"); ?>"/>
                                    <br />
                                    <input type="submit" value="Save" name="slackSave" />
                                </form>
                            </div>
                        </div>

                        <h4 class="widgettitle title-light"><span class="iconfa iconfa-leaf"></span>Zulip</h4>
                        <div class="row">
                            <div class="col-md-3">
                                <img src="<?=BASE_URL ?>/images/zulip-org-logo.png" width="200"/>
                            </div>

                            <div class="col-md-5">
                                This integration will post update notifications to the stream and topic of your choice.<br />
                                Follow the instructions <a href="https://zulipchat.com/help/add-a-bot-or-integration" target="_blank">here to create a new Bot</a>. Then paste the information into the form to the right and click Save.
                            </div>
                            <div class="col-md-4">

                                <form action="<?=BASE_URL ?>/projects/showProject/<?php echo $project['id']; ?>#integrations" method="post">
                                    <strong>Base URL</strong><br />
                                    <input type="text" name="zulipURL" id="zulipURL" placeholder="Example: https://company.zulipchat.com" value="<?php echo $this->get("zulipHook")['zulipURL']; ?>"/>
                                    <br />
                                    <strong>Bot Email</strong><br />
                                    <input type="text" name="zulipEmail" id="zulipEmail" placeholder="" value="<?php echo $this->get("zulipHook")['zulipEmail']; ?>"/>
                                    <br />
                                    <strong>Bot Key</strong><br />
                                    <input type="text" name="zulipBotKey" id="zulipBotKey" placeholder="" value="<?php echo $this->get("zulipHook")['zulipBotKey']; ?>"/>
                                    <br />
                                    <strong>Stream</strong><br />
                                    <input type="text" name="zulipStream" id="zulipStream" placeholder="" value="<?php echo $this->get("zulipHook")['zulipStream']; ?>"/>
                                    <br />
                                    <strong>Topic</strong><br />
                                    <input type="text" name="zulipTopic" id="zulipTopic" placeholder="" value="<?php echo $this->get("zulipHook")['zulipTopic']; ?>"/>
                                    <br />
                                    <input type="submit" value="Save" name="zulipSave" />
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
            window.history.pushState({},document.title, '/projects/showProject/<?php echo (int)$project['id']; ?>');
        <?php } ?>
    });
</script>