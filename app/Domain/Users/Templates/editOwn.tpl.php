<?php
defined('RESTRICTED') or die('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$roles = $tpl->get('roles');
$values = $tpl->get('values');
$user = $tpl->get('user');
?>


<div class="pageheader">

    <div class="pageicon"><span class="fa fa-user"></span></div>
    <div class="pagetitle">
        <h5><?php echo $tpl->__('label.overview') ?></h5>
        <h1><h1><?php echo $tpl->__('headlines.accountSettings'); ?></h1></h1>
    </div>
</div><!--pageheader-->

<?php echo $tpl->displayNotification(); ?>

<div class="maincontent">
    <div class="row">
        <div class="col-md-8">
            <div class="maincontentinner">
                <div class="tabbedwidget tab-primary accountTabs">

                    <ul>
                        <li><a href="#myProfile"><?php echo $tpl->__('tabs.myProfile'); ?></a></li>
                        <li><a href="#security"><?php echo $tpl->__('tabs.security'); ?></a></li>
                        <li><a href="#look"><?php echo $tpl->__('tabs.look_feel'); ?></a></li>
                        <li><a href="#notifications"><?php echo $tpl->__('tabs.notifications'); ?></a></li>
                    </ul>

                    <div id="myProfile">
                        <form action="" method="post" class='stdform'>

                            <input type="hidden" name="<?=$_SESSION['formTokenName']?>" value="<?=$_SESSION['formTokenValue']?>" />
                            <div class="row-fluid">
                                <div class="form-group">
                                    <label for="firstname" class="span3"><?php echo $tpl->__('label.firstname'); ?></label>
                                    <span class='field span6'>
                                        <input type="text" class="input" name="firstname" id="firstname" <?=$_SESSION['userdata']['isLdap'] ? "disabled='disabled'" : ''; ?>
                                               value="<?php $tpl->e($values['firstname']) ?>"/><br/>
                                    </span>
                                </div>

                                <div class="form-group">
                                    <label for="lastname" class="span3"><?php echo $tpl->__('label.lastname'); ?></label>
                                    <span class='field span6'>
                                        <input type="text" name="lastname" class="input" id="lastname" <?=$_SESSION['userdata']['isLdap'] ? "disabled='disabled'" : ''; ?>
                                               value="<?php $tpl->e($values['lastname']) ?>"/><br/>
                                    </span>
                                </div>

                                <div class="form-group">
                                    <label for="user" class="span3"><?php echo $tpl->__('label.email'); ?></label>
                                    <span class='field span6'>
                                        <input type="text" name="user" class="input" id="user" <?=$_SESSION['userdata']['isLdap'] ? "disabled='disabled'" : ''; ?>
                                               value="<?php $tpl->e($values['user']) ?>"/><br/>
                                    </span>
                                </div>

                                <div class="form-group">
                                    <label for="phone" class="span3"><?php echo $tpl->__('label.phone'); ?></label>
                                    <span class='field span6'>
                                        <input type="text" name="phone" class="input" id="phone" <?=$_SESSION['userdata']['isLdap'] ? "disabled='disabled'" : ''; ?>
                                               value="<?php $tpl->e($values['phone']) ?>"/><br/>
                                    </span>
                                </div>

                            </div>
                            <p class='stdformbutton'>
                                <input type="hidden" name="profileInfo" value="1" />

                                <input type="submit" name="save" id="save" value="<?php echo $tpl->__('buttons.save'); ?>" class="button"/>
                            </p>

                        </form>
                    </div>

                    <div id="security">
                        <h4 class="widgettitle title-light">
                            <?=$tpl->__('headlines.change_password'); ?>
                        </h4>
                        <?php if ($_SESSION['userdata']['isLdap']) {
                            echo "<strong>" . $tpl->__("text.account_managed_ldap") . "</strong><br /><br />";
                        } ?>
                        <form method="post">
                            <input type="hidden" name="<?=$_SESSION['formTokenName']?>" value="<?=$_SESSION['formTokenValue']?>" />
                            <div class="row-fluid">
                                <div class="form-group">
                                    <label for="currentPassword" class="span3"><?php echo $tpl->__('label.old_password') ?></label>
                                    <span class='field span6'>
                                        <input type='password' value="" name="currentPassword" class="input" <?=$_SESSION['userdata']['isLdap'] ? "disabled='disabled'" : ''; ?>
                                               id="currentPassword"/><br/>
                                    </span>
                                </div>

                                <div class="form-group">
                                    <label for="newPassword" class="span3"><?php echo $tpl->__('label.new_password') ?></label>
                                    <span class='field span6'>
                                        <input type='password' value="" name="newPassword" class="input" <?=$_SESSION['userdata']['isLdap'] ? "disabled='disabled'" : ''; ?>
                                               id="newPassword"/>
                                        <span id="pwStrength"></span>

                                    </span>
                                </div>

                                <div class="form-group">
                                    <label for="confirmPassword" class="span3"><?php echo $tpl->__('label.password_repeat') ?></label>
                                    <span class='field span6'>
                                        <input type="password" value="" name="confirmPassword" class="input" <?=$_SESSION['userdata']['isLdap'] ? "disabled='disabled'" : ''; ?>
                                               id="confirmPassword"/><br/>
                                        <?php if (!$_SESSION['userdata']['isLdap']) {?>
                                        <small><?=$tpl->__('label.passwordRequirements') ?></small>
                                        <?php } ?>
                                    </span>

                                </div>
                            </div>
                            <?php if (!$_SESSION['userdata']['isLdap']) {?>
                            <input type="hidden" name="savepw" value="1" />
                            <input type="submit" name="save" id="savePw" value="<?php echo $tpl->__('buttons.save'); ?>" class="button"/>
                            <?php }?>
                        </form>
                        <br /><br />
                        <h4 class="widgettitle title-light">
                            <i class="fa-solid fa-shield-halved"></i> <?=$tpl->__('headlines.twoFA'); ?>
                        </h4>
                        <?php if ($values['twoFAEnabled']) { ?>
                            <p><?php echo $tpl->__('text.twoFA_enabled'); ?></p>
                        <?php } else { ?>
                            <p><?php echo $tpl->__('text.twoFA_disabled'); ?></p>
                        <?php } ?>
                        <p><a href="<?=BASE_URL ?>/twoFA/edit"><?php echo $tpl->__('text.twoFA_manage'); ?></a></p>
                    </div>

                    <div id="look">
                        <form action="" method="post">
                            <input type="hidden" name="<?=$_SESSION['formTokenName']?>" value="<?=$_SESSION['formTokenValue']?>" />
                            <div class="row-fluid">
                                <div class="form-group">
                                    <label for="language" class="span3"><?php echo $tpl->__('label.language') ?></label>
                                    <span class='field span6'>
                                        <select name="language" id="language" style="width: 220px">
                                            <?php foreach ($tpl->get("languageList") as $languagKey => $languageValue) {?>
                                                <option value="<?=$languagKey?>" <?php if ($tpl->get('userLang') == $languagKey) {
                                                    echo "selected='selected'";
                                                               } ?>><?=$languageValue?></option>
                                            <?php } ?>
                                        </select>
                                    </span>
                                </div>
                                <div class="form-group">
                                    <label for="theme" class="span3"><?php echo $tpl->__('label.theme') ?></label>
                                    <span class='field span6'>
                                        <select name="theme" id="theme" style="width: 220px">
                                            <?php
                                            $themeCore = app()->make(\Leantime\Core\Theme::class);
                                            $themeAll = $themeCore->getAll();
                                            foreach ($themeAll as $key => $name) {
                                                ?>
                                                <option value="<?=$key ?>" <?php if ($tpl->get('userTheme') == $key) {
                                                    echo "selected='selected'";
                                                               } ?>><?=$tpl->__($name) ?></option>
                                            <?php } ?>
                                        </select>
                                    </span>
                                </div>
                            </div>
                            <input type="hidden" name="saveLook" value="1" />
                            <input type="submit" name="save" id="saveTheme" value="<?php echo $tpl->__('buttons.save'); ?>" class="button"/>
                        </form>
                    </div>

                    <div id="notifications">
                        <form action="" method="post">
                            <input type="hidden" name="<?=$_SESSION['formTokenName']?>" value="<?=$_SESSION['formTokenValue']?>" />
                            <div class="row-fluid">
                                <div class="form-group">
                                    <label for="notifications" class="span3"><?php echo $tpl->__('label.receive_notifications') ?></label>
                                    <span class='field span6'>
                                        <input type="checkbox" value="" name="notifications" class="input"
                                               id="notifications" <?php if ($values['notifications'] == "1") {
                                                    echo " checked='checked' ";
                                                                  } ?>/> <br/>
                                    </span>
                                </div>
                                <div class="form-group">
                                    <label for="messagesfrequency" class="span3"><?php echo $tpl->__('label.messages_frequency') ?></label>
                                    <span class='field span6'>
                                        <select name="messagesfrequency" class="input" id="messagesfrequency" style="width: 220px">
                                            <option value="">--<?php echo $tpl->__('label.choose_option') ?>--</option>
                                            <option value="300" <?php if ($values['messagesfrequency'] == "300") {
                                                echo " selected ";
                                                                } ?>><?php echo $tpl->__('label.5min') ?></option>
                                            <option value="900" <?php if ($values['messagesfrequency'] == "900") {
                                                echo " selected ";
                                                                } ?>><?php echo $tpl->__('label.15min') ?></option>
                                            <option value="1800" <?php if ($values['messagesfrequency'] == "1800") {
                                                echo " selected ";
                                                                 } ?>><?php echo $tpl->__('label.30min') ?></option>
                                            <option value="3600" <?php if ($values['messagesfrequency'] == "3600") {
                                                echo " selected ";
                                                                 } ?>><?php echo $tpl->__('label.1h') ?></option>
                                            <option value="10800" <?php if ($values['messagesfrequency'] == "10800") {
                                                echo " selected ";
                                                                  } ?>><?php echo $tpl->__('label.3h') ?></option>
                                            <option value="36000" <?php if ($values['messagesfrequency'] == "36000") {
                                                echo " selected ";
                                                                  } ?>><?php echo $tpl->__('label.6h') ?></option>
                                            <option value="43200" <?php if ($values['messagesfrequency'] == "43200") {
                                                echo " selected ";
                                                                  } ?>><?php echo $tpl->__('label.12h') ?></option>
                                            <option value="86400" <?php if ($values['messagesfrequency'] == "86400") {
                                                echo " selected ";
                                                                  } ?>><?php echo $tpl->__('label.24h') ?></option>
                                            <option value="172800" <?php if ($values['messagesfrequency'] == "172800") {
                                                echo " selected ";
                                                                   } ?>><?php echo $tpl->__('label.48h') ?></option>
                                            <option value="604800" <?php if ($values['messagesfrequency'] == "604800") {
                                                echo " selected ";
                                                                   } ?>><?php echo $tpl->__('label.1w') ?></option>
                                        </select> <br/>
                                    </span>
                                </div>
                            </div>
                            <input type="hidden" name="savenotifications" value="1" />
                            <input type="submit" name="save" id="savePw" value="<?php echo $tpl->__('buttons.save'); ?>" class="button"/>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="maincontentinner center">
                <img src='<?=BASE_URL?>/api/users?profileImage=<?=$user['id']; ?>?v=<?=strtotime($user['modified'] ?? "0") ?>'  class='profileImg' alt='Profile Picture' id="previousImage"/>
                <div id="profileImg">
                </div>

                <div class="par">

                    <label><?php echo $tpl->__('label.upload') ?></label>

                    <div class='fileupload fileupload-new' data-provides='fileupload'>
                        <input type="hidden"/>
                        <div class="input-append">
                            <div class="uneditable-input span3">
                                <i class="fa-file fileupload-exists"></i>
                                <span class="fileupload-preview"></span>
                            </div>
                            <span class="btn btn-file">
                                        <span class="fileupload-new"><?php echo $tpl->__('buttons.select_file') ?></span>
                                        <span class='fileupload-exists'><?php echo $tpl->__('buttons.change') ?></span>
                                        <input type='file' name='file' onchange="leantime.usersController.readURL(this)" accept=".jpg,.png,.gif,.webp"/>
                                    </span>

                            <a href='#' class='btn fileupload-exists' data-dismiss='fileupload' onclick="leantime.usersController.clearCroppie()"><?php echo $tpl->__('buttons.remove') ?></a>
                        </div>
                        <p class='stdformbutton'>
                                    <span id="save-picture" class="btn btn-primary fileupload-exists ld-ext-right">
                                        <span onclick="leantime.usersController.saveCroppie()"><?php echo $tpl->__('buttons.save') ?></span>
                                        <span class="ld ld-ring ld-spin"></span>
                                    </span>
                            <input type="hidden" name="profileImage" value="1" />
                            <input id="picSubmit" type="submit" name="savePic" class="hidden"
                                   value="<?php echo $tpl->__('buttons.upload'); ?>"/>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">

    jQuery(document).ready(function() {

        leantime.usersController.checkPWStrength('newPassword');

        jQuery('.accountTabs').tabs();

        jQuery("#messagesfrequency").chosen();
        jQuery("#language").chosen();
        jQuery("#theme").chosen();

    });
</script>
