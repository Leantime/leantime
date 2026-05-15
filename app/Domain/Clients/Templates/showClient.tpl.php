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
                <li><a href="#clientProjects"><?php echo sprintf($tpl->__('tabs.projects_with_count'), count($tpl->get('clientProjects'))); ?></a></li>
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
                            <h4 class="widgettitle title-light">
                                <span class="fa fa-user-shield"></span>
                                <?php echo $tpl->__('subtitles.client_portal_users'); ?>
                            </h4>
                            <a href="#/users/newUser?preSelectedClient=<?= $values['id'] ?>&preSelectedRole=10"
                                class="btn btn-primary" style="margin-bottom:12px;">
                                <i class="fa fa-plus"></i> <?= $tpl->__('buttons.invite_client_user') ?>
                            </a>
                            <table class='table table-bordered'>
                                <thead>
                                    <tr>
                                        <th><?php echo $tpl->__('label.name') ?></th>
                                        <th><?php echo $tpl->__('label.email') ?></th>
                                        <th><?php echo $tpl->__('label.status') ?></th>
                                        <th><?php echo $tpl->__('label.actions') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tpl->get('portalUsers') as $user) { ?>
                                        <tr>
                                            <td><?php printf($tpl->__('text.full_name'), $tpl->escape($user['firstname']), $tpl->escape($user['lastname'])); ?></td>
                                            <td><a href='mailto:<?php $tpl->e($user['username']); ?>'><?php $tpl->e($user['username']); ?></a></td>
                                            <td>
                                                <?php if (strtolower($user['status']) === 'i') { ?>
                                                    <span class="label label-warning"><?= $tpl->__('label.invited') ?></span>
                                                <?php } elseif (strtolower($user['status']) === 'a') { ?>
                                                    <span class="label label-success"><?= $tpl->__('label.active') ?></span>
                                                <?php } else { ?>
                                                    <span class="label label-default"><?= $tpl->__('label.deactivated') ?></span>
                                                <?php } ?>
                                            </td>
                                            <td>
                                                <a href="<?= BASE_URL ?>/users/editUser/<?= $user['id'] ?>"
                                                    title="<?php echo $tpl->__('buttons.edit') ?>"
                                                    style="margin-right:8px;">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <form method="post"
                                                    action="<?= BASE_URL ?>/clients/showClient/?id=<?= $tpl->get('client')['id'] ?>"
                                                    style="display:inline;"
                                                    onsubmit="return confirm('<?= $tpl->__('text.confirm_delete_client_user') ?>');">
                                                    <input type="hidden" name="deletePortalUser" value="1">
                                                    <input type="hidden" name="userId" value="<?= (int) $user['id'] ?>">
                                                    <button type="submit"
                                                        class="btn btn-link"
                                                        style="padding:0; color:var(--accent2); border:none; background:transparent;"
                                                        title="<?php echo $tpl->__('buttons.delete') ?>">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    <?php if (count($tpl->get('portalUsers')) === 0) { ?>
                                        <tr>
                                            <td colspan="4"><?= $tpl->__('text.no_client_portal_users') ?></td>
                                        </tr>
                                    <?php } ?>
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
                            <a href="#" class="delete" onclick="if(confirm('<?php echo $tpl->__('text.confirm_client_deletion'); ?>')) { document.getElementById('deleteClientForm').submit(); } return false;"><i class="fa fa-trash"></i> <?php echo $tpl->__('links.delete') ?></a>
                            <form id="deleteClientForm" method="post" action="<?= BASE_URL ?>/clients/delClient/<?php $tpl->e($_GET['id']); ?>" style="display:none;">
                                <input type="hidden" name="del" value="1">
                            </form>
                        </div>
                    </div>

                </form>
            </div>

            <div id='clientProjects'>
                <?php
                $clientProjectIds = array_column($tpl->get('clientProjects'), 'id');
                $allProjects      = $tpl->get('allProjects');
                ?>
                <form method="post" action="<?= BASE_URL ?>/clients/showClient/<?php $tpl->e($values['id']); ?>">
                    <input type="hidden" name="saveProjects" value="1" />
                    <input type="hidden" name="<?= session('formTokenName') ?>" value="<?= session('formTokenValue') ?>" />

                    <h4 class="widgettitle title-light">
                        <span class="fa fa-suitcase"></span>
                        <?php echo $tpl->__('subtitles.projects_assigned_to_client'); ?>
                    </h4>
                    <p style="color:var(--grey); margin-bottom:15px;">
                        <?php echo $tpl->__('text.projects_assigned_hint'); ?>
                    </p>

                    <div class="scrollableItemList" style="max-height:400px; overflow-y:auto;">
                        <?php if (empty($allProjects)) { ?>
                            <p><?php echo $tpl->__('text.no_projects_available'); ?></p>
                        <?php } else { ?>
                            <?php foreach ($allProjects as $proj) { ?>
                                <div class="item" style="padding:8px 0; border-bottom:1px solid var(--main-border-color);">
                                    <input type="checkbox"
                                        name="clientProjects[]"
                                        id="proj_<?php echo $proj['id']; ?>"
                                        value="<?php echo $proj['id']; ?>"
                                        <?php if (in_array($proj['id'], $clientProjectIds)) {
                                            echo 'checked';
                                        } ?> />
                                    <label for="proj_<?php echo $proj['id']; ?>" style="margin-left:8px; font-weight:normal;">
                                        <strong><?php $tpl->e($proj['name']); ?></strong>
                                    </label>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    </div>

                    <div style="margin-top:15px;">
                        <input type="submit" value="<?php echo $tpl->__('buttons.save_project_assignments'); ?>" class="btn btn-primary" />
                    </div>
                </form>
            </div>

            <div id='comment'>

                <form method="post" action="<?= BASE_URL ?>/clients/showClient/<?php echo $tpl->e($_GET['id']); ?>#comment">
                    <input type="hidden" name="comment" value="1" />
                    <?php
                    $tpl->assign('formUrl', BASE_URL . '/clients/showClient/' . $tpl->escape($_GET['id']) . '');
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

    jQuery(document).ready(function($) {
        leantime.clientsController.initClientTabs();
    });

    <?php $tpl->dispatchTplEvent('scripts.beforeClose'); ?>
</script>
