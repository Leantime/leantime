<?php

use Leantime\Core\Theme;

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
                        <li><a href="#settings"><?php echo $tpl->__('tabs.settings'); ?></a></li>
                        <li><a href="#theme"><?php echo $tpl->__('tabs.theme'); ?></a></li>
                        <li><a href="#notifications"><?php echo $tpl->__('tabs.notifications'); ?></a></li>
                    </ul>

                    <div id="myProfile">
                        <form action="" method="post">

                            <input type="hidden" name="<?=session("formTokenName")?>" value="<?=session("formTokenValue")?>" />
                            <div class="row-fluid">
                                <div class="form-group">
                                    <label for="firstname" ><?php echo $tpl->__('label.firstname'); ?></label>
                                    <span>
                                        <input type="text" class="input" name="firstname" id="firstname" <?=session("userdata.isLdap") ? "disabled='disabled'" : ''; ?>
                                               value="<?php $tpl->e($values['firstname']) ?>"/><br/>
                                    </span>
                                </div>

                                <div class="form-group">
                                    <label for="lastname" ><?php echo $tpl->__('label.lastname'); ?></label>
                                    <span>
                                        <input type="text" name="lastname" class="input" id="lastname" <?=session("userdata.isLdap") ? "disabled='disabled'" : ''; ?>
                                               value="<?php $tpl->e($values['lastname']) ?>"/><br/>
                                    </span>
                                </div>

                                <div class="form-group">
                                    <label for="user" ><?php echo $tpl->__('label.email'); ?></label>
                                    <span>
                                        <input type="text" name="user" class="input" id="user" <?=session("userdata.isLdap") ? "disabled='disabled'" : ''; ?>
                                               value="<?php $tpl->e($values['user']) ?>"/><br/>
                                    </span>
                                </div>

                                <div class="form-group">
                                    <label for="phone" ><?php echo $tpl->__('label.phone'); ?></label>
                                    <span>
                                        <input type="text" name="phone" class="input" id="phone" <?=session("userdata.isLdap") ? "disabled='disabled'" : ''; ?>
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
                        <?php if (session("userdata.isLdap")) {
                            echo "<strong>" . $tpl->__("text.account_managed_ldap") . "</strong><br /><br />";
                        } ?>
                        <form method="post">
                            <input type="hidden" name="<?=session("formTokenName")?>" value="<?=session("formTokenValue")?>" />
                            <div class="row-fluid">
                                <div class="form-group">
                                    <label for="currentPassword" ><?php echo $tpl->__('label.old_password') ?></label>
                                    <span>
                                        <input type='password' value="" name="currentPassword" class="input" <?=session("userdata.isLdap") ? "disabled='disabled'" : ''; ?>
                                               id="currentPassword"/><br/>
                                    </span>
                                </div>

                                <div class="form-group">
                                    <label for="newPassword" ><?php echo $tpl->__('label.new_password') ?></label>
                                    <span>
                                        <input type='password' value="" name="newPassword" class="input" <?=session("userdata.isLdap") ? "disabled='disabled'" : ''; ?>
                                               id="newPassword"/>
                                        <span id="pwStrength"></span>

                                    </span>
                                </div>

                                <div class="form-group">
                                    <label for="confirmPassword" ><?php echo $tpl->__('label.password_repeat') ?></label>
                                    <span>
                                        <input type="password" value="" name="confirmPassword" class="input" <?=session("userdata.isLdap") ? "disabled='disabled'" : ''; ?>
                                               id="confirmPassword"/><br/>
                                        <?php if (!session("userdata.isLdap")) {?>
                                        <small><?=$tpl->__('label.passwordRequirements') ?></small>
                                        <?php } ?>
                                    </span>

                                </div>
                            </div>
                            <?php if (!session("userdata.isLdap")) {?>
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

                    <div id="settings">
                        <form action="" method="post">
                            <input type="hidden" name="<?=session("formTokenName")?>" value="<?=session("formTokenValue")?>" />
                            <div class="row-fluid">
                                <div class="form-group">
                                    <label for="language" ><?php echo $tpl->__('label.language') ?></label>
                                    <span class='field'>
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
                                    <label for="date_format" ><?php echo $tpl->__('label.date_format') ?></label>
                                    <span>
                                        <select name="date_format" id="date_format" style="width: 220px">
                                            <?php
                                            $dateFormats = $tpl->get('dateTimeValues')['dates'];
                                            $dateTimeNow = date_create();

                                            foreach ($dateFormats as $format) {
                                                ?>
                                                <option value="<?php echo ($format); ?>" <?php if ($tpl->get('dateFormat') == $format) {
                                                                                            echo "selected='selected'";
                                                               } ?>><?php echo (date_format($dateTimeNow, $format)); ?></option>
                                            <?php } ?>
                                        </select>
                                    </span>
                                </div>
                                <div class="form-group">
                                    <label for="time_format" ><?php echo $tpl->__('label.time_format') ?></label>
                                    <span>
                                        <select name="time_format" id="time_format" style="width: 220px">
                                            <?php
                                            $timeFormats = $tpl->get('dateTimeValues')['times'];
                                            $dateTimeNow = date_create();

                                            foreach ($timeFormats as $format) {
                                                ?>
                                                <option value="<?php echo ($format); ?>" <?php if ($tpl->get('timeFormat') == $format) {
                                                                                            echo "selected='selected'";
                                                               } ?>><?php echo (date_format($dateTimeNow, $format)); ?></option>
                                            <?php } ?>
                                        </select>
                                    </span>
                                </div>
                                <div class="form-group">
                                    <label for="timezone" ><?php echo $tpl->__('label.timezone') ?></label>
                                    <span>
                                        <select name="timezone" id="timezone" style="width: 220px">
                                            <?php
                                            $userTZ = $tpl->get('timezone');
                                            $TZlist = $tpl->get('timezoneOptions');

                                            foreach ($TZlist as $tz) {
                                                ?>
                                                <option value="<?php echo ($tz); ?>" <?php if ($userTZ === $tz) {
                                                                                            echo "selected='selected'";
                                                               } ?>><?php echo ($tz); ?></option>
                                            <?php } ?>
                                        </select>
                                    </span>
                                </div>
                            </div>
                            <input type="hidden" name="saveSettings" value="1" />
                            <input type="submit" name="save" id="saveSettings" value="<?php echo $tpl->__('buttons.save'); ?>" class="button"/>
                        </form>
                    </div>

                    <div id="theme">
                        <form action="" method="post">
                            <input type="hidden" name="<?=session("formTokenName")?>" value="<?=session("formTokenValue")?>" />
                            <div class="row-fluid">
                                <div class="form-group">
                                    <label for="themeSelect" ><?php echo $tpl->__('label.theme') ?></label>
                                    <span class='field'>
                                        <select name="theme" id="themeSelect" style="width: 220px">
                                            <?php
                                            $themeCore = app()->make(Theme::class);
                                            $themeAll = $themeCore->getAll();
                                            foreach ($themeAll as $key => $theme) {
                                                ?>
                                                <option value="<?= $key ?>" <?php if ($tpl->get('userTheme') == $key) {
                                                    echo "selected='selected'";
                                                               } ?>><?= $tpl->__($theme['name']) ?></option>
                                            <?php } ?>
                                        </select>

                                    </span>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <label for="colormode" ><?php echo $tpl->__('label.colormode') ?></label>
                                        <select name="colormode" id="colormode">
                                           <option value="light" <?php if ($tpl->get('userColorMode') == "light") {
                                                echo "selected='selected'";
                                                                 } ?>><?= $tpl->__('label.light') ?></option>
                                           <option value="dark" <?php if ($tpl->get('userColorMode') == "dark") {
                                                echo "selected='selected'";
                                                                } ?>><?= $tpl->__('label.dark') ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <hr />
                                        <label>Font</label>
                                        <?php foreach ($tpl->get('availableFonts') as $key => $font) { ?>
                                            <div class="font-preview">
                                                <label for="font-<?=$key?>" class="font"
                                                       style="font-family:'<?=$font ?>'; ">
                                                    The quick brown fox jumps over the lazy dog
                                                </label>
                                                <span class="font-name">
                                                    <input type="radio" name="themeFont" id="font-<?=$key?>" value="<?=$font ?>" <?php if ($tpl->get('themeFont') == $key) {
                                                        echo "checked='checked'";
                                                                                                  } ?>/>
                                                    <label for="color-<?=$key?>"><?=$font ?></label>
                                            </div>
                                        <?php } ?>

                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <hr />
                                        <label>Color Scheme</label>
                                        <?php foreach ($tpl->get('availableColorSchemes') as $key => $scheme) { ?>
                                            <div class="color-circle">
                                                <label for="color-<?=$key?>" class="color"
                                                      style="background:linear-gradient(135deg, <?=$scheme["primaryColor"] ?> 20%, <?=$scheme["secondaryColor"] ?> 100%);">
                                                </label>
                                                <span class="color-name">
                                                    <input type="radio" name="colorscheme" id="color-<?=$key?>" value="<?=$key ?>" <?php if ($tpl->get('userColorScheme') == $key) {
                                                        echo "checked='checked'";
                                                                                                     } ?>/>
                                                    <label for="color-<?=$key?>"><?=$tpl->__($scheme["name"]) ?></label>
                                            </div>
                                        <?php } ?>

                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="saveTheme" value="1" />
                            <input type="submit" name="save" id="saveTheme" value="<?php echo $tpl->__('buttons.save'); ?>" class="button"/>
                        </form>
                    </div>

                    <div id="notifications">
                        <form action="" method="post">
                            <input type="hidden" name="<?=session("formTokenName")?>" value="<?=session("formTokenValue")?>" />
                            <div class="row-fluid">
                                <div class="form-group">
                                    <label for="notifications" ><?php echo $tpl->__('label.receive_notifications') ?></label>
                                    <span>
                                        <input type="checkbox" value="" name="notifications" class="input"
                                               id="notifications" <?php if ($values['notifications'] == "1") {
                                                    echo " checked='checked' ";
                                                                  } ?>/> <br/>
                                    </span>
                                </div>
                                <div class="form-group">
                                    <label for="messagesfrequency" ><?php echo $tpl->__('label.messages_frequency') ?></label>
                                    <span>
                                        <select name="messagesfrequency" class="input" id="messagesfrequency" style="width: 220px">
                                            <option value="">--<?php echo $tpl->__('label.choose_option') ?>--</option>
                                             <option value="60" <?php if ($values['messagesfrequency'] == "60") {
                                                    echo " selected ";
                                                                } ?>><?php echo $tpl->__('label.1min') ?></option>
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
                            <input type="submit" name="save" value="<?php echo $tpl->__('buttons.save'); ?>" class="button"/>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="maincontentinner center">
                <img src='<?=BASE_URL?>/api/users?profileImage=<?=$user['id']; ?>?v=<?=format($user['modified'])->timestamp() ?>'  class='profileImg' alt='Profile Picture' id="previousImage"/>
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
        jQuery("#themeSelect").chosen();

    });
</script>
