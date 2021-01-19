<?php

defined('RESTRICTED') or die('Restricted access');
$values = $this->get('client');
$users = $this->get('users');
?>


<div class="pageheader">


<div class="pageicon"><span class="fa fa-address-book"></span></div>
    <div class="pagetitle">
        <h5><?php echo $this->__('label.administration') ?></h5>
        <h1><?php $this->e($values['name']); ?></h1>
    </div>
</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">
        <?php echo $this->displayNotification(); ?>

        <div class="tabbedwidget tab-primary clientTabs">

            <ul>
                <li><a href="#clientDetails"><?php echo $this->__('label.client_details'); ?></a></li>
                <li><a href="#comment"><?php echo sprintf($this->__('tabs.discussion_with_count'), count($this->get('comments'))); ?></a></li>
                <li><a href="#files"><?php echo sprintf($this->__('tabs.files_with_count'), count($this->get('files'))); ?></a></li>
            </ul>

            <div id='clientDetails'>
                <form action="" method="post">
                    <div class="row row-fluid">
                        <div class="col-md-6">
                            <h4 class="widgettitle title-light"><span class="iconfa iconfa-leaf"></span> <?php echo $this->__('subtitle.details'); ?></h4>

                            <div class="form-group">
                                <label class="span4 control-label"><?php echo $this->__('label.name') ?></label>
                                <div class="span6">
                                    <input type="text" name="name" id="name" value="<?php $this->e($values['name']); ?>" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="span4 control-label"><?php echo $this->__('label.email') ?></label>
                                <div class="span6">
                                    <input type="text" name="email" id="email" value="<?php $this->e($values['email']); ?>" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="span4 control-label"><?php echo $this->__('label.url') ?></label>
                                <div class="span6">
                                    <input
                                            type="text" name="internet" id="internet"
                                            value="<?php $this->e($values['internet']); ?>" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="span4 control-label"><?php echo $this->__('label.street') ?></label>
                                <div class="span6">
                                    <input
                                            type="text" name="street" id="street"
                                            value="<?php $this->e($values['street']); ?>" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="span4 control-label"><?php echo $this->__('label.zip') ?></label>
                                <div class="span6">
                                    <input type="text"
                                    name="zip" id="zip" value="<?php $this->e($values['zip']); ?>" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="span4 control-label"><?php echo $this->__('label.city') ?></label>
                                <div class="span6">
                                    <input type="text"
                                           name="city" id="city" value="<?php $this->e($values['city']); ?>" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="span4 control-label"><?php echo $this->__('label.state') ?></label>
                                <div class="span6">
                                    <input
                                            type="text" name="state" id="state"
                                            value="<?php $this->e($values['state']); ?>" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="span4 control-label"><?php echo $this->__('label.country') ?></label>
                                <div class="span6">
                                    <input
                                            type="text" name="country" id="country"
                                            value="<?php $this->e($values['country']); ?>" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="span4 control-label"><?php echo $this->__('label.phone') ?></label>
                                <div class="span6">
                                    <input
                                            type="text" name="phone" id="phone"
                                            value="<?php $this->e($values['phone']); ?>" />
                                </div>
                            </div>

                        </div>

                        <div class="col-md-6">
                            <h4 class="widgettitle title-light"><span class="fa fa-users"></span> <?php echo $this->__('subtitles.users_assigned_to_this_client') ?></h4>

                            <table class='table table-bordered'>
                                <colgroup>
                                    <col class="con1" />
                                    <col class="con0"/>
                                    <col class="con1" />
                                </colgroup>
                                <thead>
                                <tr>
                                    <th><?php echo $this->__('label.name') ?></th>
                                    <th><?php echo $this->__('label.email') ?></th>
                                    <th><?php echo $this->__('label.phone') ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach($this->get('userClients') as $user): ?>
                                    <tr>
                                        <td>
                                        <?php printf( $this->__('text.full_name'), $this->escape($user['firstname']), $this->escape($user['lastname'])); ?>
                                        </td>
                                        <td><a href='mailto:<?php $this->e($user['username']); ?>'><?php $this->e($user['username']); ?></a></td>
                                        <td><?php $this->e($user['phone']); ?></td>
                                    </tr>
                                <?php endforeach; ?>

                                <?php if(count($this->get('userClients')) == 0) {
                                    echo "<tr><td colspan='3'>".$this->__('text.no_users_assigned_to_this_client')."</td></tr>";
                                }?>
                                </tbody>
                            </table>

                        </div>

                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <input type="submit" name="save" id="save"
                                   value="<?php echo $this->__('buttons.save') ?>" class="btn btn-primary" />
                        </div>
                        <div class="col-md-6 align-right">
                            <a href="<?=BASE_URL ?>/clients/delClient/<?php $this->e($_GET['id']); ?>" class="delete"><i class="fa fa-trash"></i> <?php echo $this->__('links.delete') ?></a>
                        </div>
                    </div>

                </form>
            </div>

            <div id='comment'>

                <form method="post" action="<?=BASE_URL ?>/clients/showClient/<?php echo $this->e($_GET['id']); ?>#comment">
                    <input type="hidden" name="comment" value="1" />
                    <?php
                    $this->assign('formUrl', BASE_URL."/clients/showClient/".$this->escape($_GET['id'])."");
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
                                                        <li><a href="<?=BASE_URL ?>/clients/showClient/<?php echo $this->e($_GET['id']); ?>?delFile=<?php echo $file['id'] ?>" class="delete"><i class="fa fa-trash"></i> <?php echo $this->__("links.delete"); ?></a></li>
                                                    <?php  } ?>

                                                </ul>
                                            </div>
                                              <a class="cboxElement" href="<?=BASE_URL ?>/download.php?module=<?php echo $file['module'] ?>&encName=<?php echo $file['encName'] ?>&ext=<?php $this->e($file['extension']); ?>&realName=<?php $this->e($file['realName']); ?>">
                                                  <?php if (in_array(strtolower($file['extension']), $this->get('imgExtensions'))) :  ?>
                                                      <img style='max-height: 50px; max-width: 70px;' src="<?=BASE_URL ?>/download.php?module=<?php echo $file['module'] ?>&encName=<?php echo $file['encName'] ?>&ext=<?php $this->e($file['extension']); ?>&realName=<?php $this->e($file['realName']); ?>" alt="" />
                                                    <?php else: ?>
                                                      <img style='max-height: 50px; max-width: 70px;' src='<?=BASE_URL ?>/images/thumbs/doc.png' />
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
    <?php echo $this->displayLink('projects.newProject', $this->__('NEW_PROJECT'), null, array('class' => 'btn btn-primary btn-rounded')) ?><br/>
    <table class='table table-bordered'>
     <colgroup>
            <col class="con0"/>
          <col class="con1" />
            <col class="con0"/>
          <col class="con1" />
     </colgroup>
     <thead>
         <tr>
             <th><?php echo $this->__('ID') ?></th>
             <th><?php echo $this->__('TITLE') ?></th>
             <th><?php echo $this->__('OPEN_TICKETS') ?></th>
             <th><?php echo $this->__('HOUR_BUDGET') ?></th>
         </tr>
     </thead>
     <tbody>
    <?php foreach($this->get('clientProjects') as $project): ?>
        <?php if(isset($project['id']) && $project['id'] > 0) : ?>
            <tr>
                <td><?php echo $project['id'] ?></td>
                <td><a href="/projects/showProject/<?php echo $project['id']?>"><?php $this->e($project['name']); ?></a></td>
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

<script type="text/javascript">
    jQuery(document).ready(function($)
        {
            leantime.clientsController.initClientTabs();

        }
    );

</script>
