<?php

defined('RESTRICTED') or die('Restricted access');
$values = $this->get('client');
$users = $this->get('users');
?>
<script type="text/javascript">
    jQuery(document).ready(function($) 
        {
            jQuery('.tabbedwidget').tabs();

        } 
    ); 
    
</script>

<div class="pageheader">
           
            
            <div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
            <div class="pagetitle">
                <h5>Administration</h5>
                <h1><?php $this->e($values['name']); ?></h1>
            </div>
        </div><!--pageheader-->
        
        <div class="maincontent">
            <div class="maincontentinner">
                <?php echo $this->displayNotification(); ?>
                <div class="tabbedwidget tab-primary">

        <ul>
            <li><a href="#clientDetails"><?php echo $language->lang_echo('CLIENT_DETAILS'); ?></a></li>
            <li><a href="#comment">Discussion (<?php echo count($this->get('comments')) ?>)</a></li>
            <li><a href="#files"><?php echo $language->lang_echo('FILES'); ?> (<?php echo count($this->get('files')) ?>)</a></li>


        </ul>

        <div id='clientDetails'>
            <form action="" method="post">

                <div class="row row-fluid">
                    <div class="col-md-6">
                        <h4 class="widgettitle title-light"><span class="iconfa iconfa-leaf"></span> Details</h4>

                        <div class="form-group">
                            <label class="span4 control-label"><?php echo $language->lang_echo('NAME') ?></label>
                            <div class="span6">
                                <input type="text" name="name" id="name" value="<?php $this->e($values['name']); ?>" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="span4 control-label"><?php echo $language->lang_echo('EMAIL') ?></label>
                            <div class="span6">
                                <input type="text" name="email" id="email" value="<?php $this->e($values['email']); ?>" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="span4 control-label"><?php echo $language->lang_echo('URL') ?></label>
                            <div class="span6">
                                <input
                                        type="text" name="internet" id="internet"
                                        value="<?php $this->e($values['internet']); ?>" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="span4 control-label"><?php echo $language->lang_echo('STREET') ?></label>
                            <div class="span6">
                                <input
                                        type="text" name="street" id="street"
                                        value="<?php $this->e($values['street']); ?>" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="span4 control-label"><?php echo $language->lang_echo('ZIP') ?></label>
                            <div class="span6">
                                <input type="text"
                                name="zip" id="zip" value="<?php $this->e($values['zip']); ?>" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="span4 control-label"><?php echo $language->lang_echo('CITY') ?></label>
                            <div class="span6">
                                <input type="text"
                                       name="city" id="city" value="<?php $this->e($values['city']); ?>" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="span4 control-label"><?php echo $language->lang_echo('STATE') ?></label>
                            <div class="span6">
                                <input
                                        type="text" name="state" id="state"
                                        value="<?php $this->e($values['state']); ?>" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="span4 control-label"><?php echo $language->lang_echo('COUNTRY') ?></label>
                            <div class="span6">
                                <input
                                        type="text" name="country" id="country"
                                        value="<?php $this->e($values['country']); ?>" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="span4 control-label"><?php echo $language->lang_echo('PHONE') ?></label>
                            <div class="span6">
                                <input
                                        type="text" name="phone" id="phone"
                                        value="<?php $this->e($values['phone']); ?>" />
                            </div>
                        </div>



                    </div>

                    <div class="col-md-6">
                        <h4 class="widgettitle title-light"><span class="fa fa-users"></span> Contacts/Stakeholders</h4>

                        <table class='table table-bordered'>
                            <colgroup>
                                <col class="con1" />
                                <col class="con0"/>
                                <col class="con1" />
                            </colgroup>
                            <thead>
                            <tr>
                                <th><?php echo $language->lang_echo('NAME') ?></th>
                                <th><?php echo $language->lang_echo('EMAIL') ?></th>
                                <th><?php echo $language->lang_echo('PHONE') ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach($this->get('userClients') as $user): ?>
                                <tr>
                                    <td><?php $this->e($user['firstname']. ' ' .$user['lastname']); ?></td>
                                    <td><a href='mailto:<?php $this->e($user['username']); ?>'><?php $this->e($user['username']); ?></a></td>
                                    <td><?php $this->e($user['phone']); ?></td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if(count($this->get('userClients')) == 0) {
                                echo "<tr><td colspan='3'>No contacts have been added to this client. Add and assign users <a href='".BASE_URL."/users/showAll'>here</a></td></tr>";
                            }?>
                            </tbody>
                        </table>

                    </div>

                </div>

                <div class="row">
                    <div class="col-md-6">
                        <input type="submit" name="save" id="save"
                               value="<?php echo $language->lang_echo('SAVE') ?>" class="btn btn-primary" />
                    </div>
                    <div class="col-md-6 align-right">
                        <a href="<?=BASE_URL ?>/clients/delClient/<?php $this->e($_GET['id']); ?>" class="delete"><i class="fa fa-trash"></i> Delete Client</a>
                    </div>
                </div>

            </form>
        </div>

        <div id='comment'>

            <form method="post" action="<?=BASE_URL ?>/clients/showClient/<?php echo $this->e($_GET['id']); ?>#comment">
                <input type="hidden" name="comment" value="1" />
                <?php
                $this->assign('formUrl', "/clients/showClient/".$this->escape($_GET['id'])."");
                $this->displaySubmodule('comments-generalComment') ?>
            </form>


        </div>

        <div id='files'>

            <div class="mediamgr_category">
                <form action='#files' method='POST' enctype="multipart/form-data">

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
                                          <a class="cboxElement" href="<?=BASE_URL ?>/download.php?module=<?php echo $file['module'] ?>&encName=<?php echo $file['encName'] ?>&ext=<?php $this->e($file['extension']); ?>&realName=<?php $this->e($file['realName']); ?>">
                                              <?php if (in_array(strtolower($file['extension']), $this->get('imgExtensions'))) :  ?>
                                                  <img style='max-height: 50px; max-width: 70px;' src="<?=BASE_URL ?>/download.php?module=<?php echo $file['module'] ?>&encName=<?php echo $file['encName'] ?>&ext=<?php $this->e($file['extension']); ?>&realName=<?php $this->e($file['realName']); ?>" alt="" />
                                                <?php else: ?>
                                                  <img style='max-height: 50px; max-width: 70px;' src='<?=BASE_URL ?>/includes/templates/zypro/images/thumbs/doc.png' />
                                                <?php endif; ?>
                                            <span class="filename"><?php $this->e($file['realName']); ?></span>
                                          </a>
                                       </li>
                                <?php endforeach; ?>
                                <br class="clearall" />
                                </ul>

            </div><!--mediamgr_content-->
            <div style='clear:both'>&nbsp;</div>


        </div>

                    <?php /*
        <div id='projects'>
            <?php echo $this->displayLink('projects.newProject', $language->lang_echo('NEW_PROJECT'), null, array('class' => 'btn btn-primary btn-rounded')) ?><br/>
            <table class='table table-bordered'>
             <colgroup>
                    <col class="con0"/>
                  <col class="con1" />
                    <col class="con0"/>
                  <col class="con1" />
             </colgroup>
             <thead>
                 <tr>
                     <th><?php echo $language->lang_echo('ID') ?></th>
                     <th><?php echo $language->lang_echo('TITLE') ?></th>
                     <th><?php echo $language->lang_echo('OPEN_TICKETS') ?></th>
                     <th><?php echo $language->lang_echo('HOUR_BUDGET') ?></th>
                 </tr>
             </thead>
             <tbody>
            <?php foreach($this->get('clientProjects') as $project): ?>
                <?php if(isset($project['id']) && $project['id'] > 0) : ?>
                    <tr>
                        <td><?php echo $project['id'] ?></td>
                        <td><a href="<?=BASE_URL ?>/projects/showProject/<?php echo $project['id']?>"><?php $this->e($project['name']); ?></a></td>
                        <td><?php echo $project['numberOfTickets'] ?></td>
                        <td><?php $this->e($project['hourBudget']) ?></td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
             </tbody>
            </table>

        </div>*/?>

        </div>
            </div>
        </div>
