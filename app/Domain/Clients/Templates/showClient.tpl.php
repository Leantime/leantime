<?php

defined('RESTRICTED') or exit('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$values = $tpl->get('client');
$users = $tpl->get('users');
?>

<?php $tpl->dispatchTplEvent('beforePageHeaderOpen'); ?>
<div class="pageheader">
    <?php $tpl->dispatchTplEvent('afterPageHeaderOpen'); ?>
    <div class="pageicon"><span class="fa fa-address-book"></span></div>
    <div class="pagetitle">
        <h5><?php echo $tpl->__('label.administration') ?></h5>
        <h1><?php $tpl->e($values['name']); ?></h1>
    </div>
    <?php $tpl->dispatchTplEvent('beforePageHeaderClose'); ?>
</div><!--pageheader-->
<?php $tpl->dispatchTplEvent('afterPageHeaderClose'); ?>

<div class="maincontent">
    <div class="maincontentinner">
        <?php echo $tpl->displayNotification(); ?>

        <div class="tabbedwidget tab-primary clientTabs">

            <ul>
                <li><a href="#clientDetails"><?php echo $tpl->__('label.client_details'); ?></a></li>
                <li><a href="#comment"><?php echo sprintf($tpl->__('tabs.discussion_with_count'), count($tpl->get('comments'))); ?></a></li>
                <li><a href="#files"><?php echo sprintf($tpl->__('tabs.files_with_count'), count($tpl->get('files'))); ?></a></li>
            </ul>

            <div id='clientDetails'>
                <form action="" method="post">

                    <div class="row row-fluid">
                        <div class="col-md-6">
                            <h4 class="widgettitle title-light"><span class="fa fa-leaf"></span> <?php echo $tpl->__('subtitle.details'); ?></h4>

                            <div class="form-group">
                                <label class=" control-label"><?php echo $tpl->__('label.client_id') ?></label>
                                <div class="">
                                    <input type="text" name="id" id="id" value="<?php $tpl->e($values['id']); ?>" readonly />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class=" control-label"><?php echo $tpl->__('label.name') ?></label>
                                <div class="">
                                    <input type="text" name="name" id="name" value="<?php $tpl->e($values['name']); ?>" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class=" control-label"><?php echo $tpl->__('label.email') ?></label>
                                <div class="">
                                    <input type="text" name="email" id="email" value="<?php $tpl->e($values['email']); ?>" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class=" control-label"><?php echo $tpl->__('label.url') ?></label>
                                <div class="">
                                    <input
                                            type="text" name="internet" id="internet"
                                            value="<?php $tpl->e($values['internet']); ?>" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class=" control-label"><?php echo $tpl->__('label.street') ?></label>
                                <div class="">
                                    <input
                                            type="text" name="street" id="street"
                                            value="<?php $tpl->e($values['street']); ?>" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class=" control-label"><?php echo $tpl->__('label.zip') ?></label>
                                <div class="">
                                    <input type="text"
                                    name="zip" id="zip" value="<?php $tpl->e($values['zip']); ?>" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class=" control-label"><?php echo $tpl->__('label.city') ?></label>
                                <div class="">
                                    <input type="text"
                                           name="city" id="city" value="<?php $tpl->e($values['city']); ?>" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class=" control-label"><?php echo $tpl->__('label.state') ?></label>
                                <div class="">
                                    <input
                                            type="text" name="state" id="state"
                                            value="<?php $tpl->e($values['state']); ?>" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class=" control-label"><?php echo $tpl->__('label.country') ?></label>
                                <div class="">
                                    <input
                                            type="text" name="country" id="country"
                                            value="<?php $tpl->e($values['country']); ?>" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class=" control-label"><?php echo $tpl->__('label.phone') ?></label>
                                <div class="">
                                    <input
                                            type="text" name="phone" id="phone"
                                            value="<?php $tpl->e($values['phone']); ?>" />
                                </div>
                            </div>

                        </div>

                        <div class="col-md-6">
                            <h4 class="widgettitle title-light"><span class="fa fa-users"></span> <?php echo $tpl->__('subtitles.users_assigned_to_this_client') ?></h4>
                            <a href="#/users/newUser?preSelectedClient=<?= $values['id'] ?>" class="btn btn-primary"><i class='fa fa-plus'></i> <?= $tpl->__('buttons.add_user') ?> </a>
                            <table class='table table-bordered'>
                                <colgroup>
                                    <col class="con1" />
                                    <col class="con0"/>
                                    <col class="con1" />
                                </colgroup>
                                <thead>
                                <tr>
                                    <th><?php echo $tpl->__('label.name') ?></th>
                                    <th><?php echo $tpl->__('label.email') ?></th>
                                    <th><?php echo $tpl->__('label.phone') ?></th>
                                    <th><?php echo $tpl->__('label.actions') ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($tpl->get('userClients') as $user) { ?>
                                    <tr>
                                        <td>
                                        <?php printf($tpl->__('text.full_name'), $tpl->escape($user['firstname']), $tpl->escape($user['lastname'])); ?>
                                        </td>
                                        <td><a href='mailto:<?php $tpl->e($user['username']); ?>'><?php $tpl->e($user['username']); ?></a></td>
                                        <td><?php $tpl->e($user['phone']); ?></td>
                                        <td>
                                            <a href="<?= BASE_URL ?>/users/editUser/<?= $user['id'] ?>" title="<?php echo $tpl->__('buttons.edit') ?>">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/clients/removeUser/<?= $values['id'] ?>/<?= $user['id'] ?>"
                                               class="delete"
                                               title="<?php echo $tpl->__('buttons.remove') ?>"
                                               onclick="return confirm('<?php echo $tpl->__('text.confirm_remove_user_from_client') ?>')">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php } ?>

                                <?php if (count($tpl->get('userClients')) == 0) {
                                    echo "<tr><td colspan='4'>".$tpl->__('text.no_users_assigned_to_this_client').'</td></tr>';
                                }?>
                                </tbody>
                            </table>

                        </div>

                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <input type="submit" name="save" id="save"
                                   value="<?php echo $tpl->__('buttons.save') ?>" class="btn btn-primary" />
                        </div>
                        <div class="col-md-6 align-right">
                            <a href="<?= BASE_URL ?>/clients/delClient/<?php $tpl->e($_GET['id']); ?>" class="delete"><i class="fa fa-trash"></i> <?php echo $tpl->__('links.delete') ?></a>
                        </div>
                    </div>

                </form>
            </div>

            <div id='comment'>

                <form method="post" action="<?= BASE_URL ?>/clients/showClient/<?php echo $tpl->e($_GET['id']); ?>#comment">
                    <input type="hidden" name="comment" value="1" />
                    <?php
                    $tpl->assign('formUrl', BASE_URL.'/clients/showClient/'.$tpl->escape($_GET['id']).'');
$tpl->displaySubmodule('comments-generalComment') ?>
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
                                        <i class="fa-file fileupload-exists"></i><span class="fileupload-preview"></span>
                                    </div>
                                    <span class="btn btn-file">
                                                         <span class="fileupload-new"><?= $tpl->__('label.select_file'); ?></span>
                                                <span class='fileupload-exists'><?= $tpl->__('label.change'); ?></span>
                                                        <input type='file' name='file' />
                                                    </span>
                                    <a href='#' class='btn fileupload-exists' data-dismiss='fileupload'><?= $tpl->__('buttons.remove'); ?></a>
                                </div>
                            </div>
                        </div>

                        <input type="submit" name="upload" class="button" value="<?= $tpl->__('buttons.upload'); ?>" />

                    </form>
                </div>

                <div class="mediamgr_content">

                    <ul id='medialist' class='listfile'>
                                    <?php foreach ($tpl->get('files') as $file) { ?>
                                        <li class="<?php echo $file['moduleId'] ?>">
                                            <div class="inlineDropDownContainer" style="float:right;">

                                                <a href="javascript:void(0);" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                                                    <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                                </a>
                                                <ul class="dropdown-menu">
                                                    <li class="nav-header"><?php echo $tpl->__('subtitles.file'); ?></li>
                                                    <li><a href="<?= BASE_URL ?>/files/get?module=<?php echo $file['module'] ?>&encName=<?php echo $file['encName'] ?>&ext=<?php echo $file['extension'] ?>&realName=<?php echo $file['realName'] ?>"><?php echo $tpl->__('links.download'); ?></a></li>

                                                    <?php
                                if ($login::userIsAtLeast($roles::$admin)) { ?>
                                                        <li><a href="<?= BASE_URL ?>/clients/showClient/<?php echo $tpl->e($_GET['id']); ?>?delFile=<?php echo $file['id'] ?>" class="delete"><i class="fa fa-trash"></i> <?php echo $tpl->__('links.delete'); ?></a></li>
                                                    <?php } ?>

                                                </ul>
                                            </div>
                                              <a class="cboxElement" href="<?= BASE_URL ?>/files/get?module=<?php echo $file['module'] ?>&encName=<?php echo $file['encName'] ?>&ext=<?php $tpl->e($file['extension']); ?>&realName=<?php $tpl->e($file['realName']); ?>">
                                                  <?php if (in_array(strtolower($file['extension']), $tpl->get('imgExtensions'))) {  ?>
                                                      <img style='max-height: 50px; max-width: 70px;' src="<?= BASE_URL ?>/files/get?module=<?php echo $file['module'] ?>&encName=<?php echo $file['encName'] ?>&ext=<?php $tpl->e($file['extension']); ?>&realName=<?php $tpl->e($file['realName']); ?>" alt="" />
                                                  <?php } else { ?>
                                                      <img style='max-height: 50px; max-width: 70px;' src='<?= BASE_URL ?>/dist/images/thumbs/doc.png' />
                                                  <?php } ?>
                                                <span class="filename"><?php $tpl->e($file['realName']); ?></span>
                                              </a>
                                           </li>
                                    <?php } ?>
                                    <br class="clearall" />
                                    </ul>

                </div><!--mediamgr_content-->
                <div style='clear:both'>&nbsp;</div>


            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

    <?php $tpl->dispatchTplEvent('scripts.afterOpen'); ?>

    jQuery(document).ready(function($)
        {
            leantime.clientsController.initClientTabs();
        }
    );

    <?php $tpl->dispatchTplEvent('scripts.beforeClose'); ?>

</script>
