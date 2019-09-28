<?php
defined('RESTRICTED') or die('Restricted access');
$roles = $this->get('roles');
$values = $this->get('values');
$user = $this->get('user');
?>


<div class="pageheader">

    <div class="pageicon"><span class="fa fa-user"></span></div>
    <div class="pagetitle">
        <h5><?php echo $language->lang_echo('OVERVIEW'); ?></h5>
        <h1><?php echo $language->lang_echo('EDIT_MY_DATA'); ?></h1>
    </div>
</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">

        <?php echo $this->displayNotification(); ?>

        <div class="row-fluid">
            <div class="span7">

                <div class="widget">
                    <h4 class="widgettitle"><?php echo $language->lang_echo('OVERVIEW'); ?></h4>
                    <div class="widgetcontent">

                        <form action="" method="post" class='stdform'>

                            <p>
                                <label for="firstname"><?php echo $language->lang_echo('FIRSTNAME'); ?></label>
                                <span class='field'>
                                    <input type="text" class="input" name="firstname" id="firstname"
                                           value="<?php echo $values['firstname'] ?>"/><br/>
                                </span>
                            </p>

                            <p>
                                <label for="lastname"><?php echo $language->lang_echo('LASTNAME'); ?></label>
                                <span class='field'>
                                    <input type="text" name="lastname" class="input" id="lastname"
                                           value="<?php echo $values['lastname'] ?>"/><br/>
                                </span>
                            </p>

                            <p>
                                <label for="user"><?php echo $language->lang_echo('EMAIL'); ?></label>
                                <span class='field'>
                                    <input type="text" name="user" class="input" id="user"
                                           value="<?php echo $values['user'] ?>"/><br/>
                                </span>
                            </p>

                            <p>
                                <label for="phone"><?php echo $language->lang_echo('PHONE'); ?></label>
                                <span class='field'>
                                    <input type="text" name="phone" class="input" id="phone"
                                           value="<?php echo $values['phone'] ?>"/><br/>
                                </span>
                            </p>

                            <p>
                                <label for="currentPassword"><?php echo $language->lang_echo('OLD_PASSWORD') ?></label>
                                <span class='field'>
                                    <input type='password' value="" name="currentPassword" class="input"
                                           id="currentPassword"/><br/>
                                </span>
                            </p>

                            <p>
                                <label for="oldPassword"><?php echo $language->lang_echo('NEW_PASSWORD') ?></label>
                                <span class='field'>
                                    <input type='password' value="" name="newPassword" class="input"
                                           id="newPassword"/><br/>
                                </span>
                            </p>

                            <p>
                                <label for="newPassword"><?php echo $language->lang_echo('NEW_PASSWORD2') ?></label>
                                <span class='field'>
                                    <input type="password" value="" name="confirmPassword" class="input"
                                           id="confirmPassword"/><br/>
                                </span>
                            </p>

                            <p>
                                <label for="notifications"><?php echo $language->lang_echo('RECEIVE_NOTIFICATIONS') ?></label>
                                <span class='field'>
                                    <input type="checkbox" value="" name="notifications" class="input"
                                           id="notifications" <?php if ($values['notifications'] == "1") echo " checked='checked' "; ?>/> <br/>
                                </span>
                            </p>

                            <p class='stdformbutton'>
                                <input type="submit" name="save" id="save"
                                       value="<?php echo $language->lang_echo('SAVE'); ?>" class="button"/>
                            </p>

                        </form>

                    </div>
                </div>
            </div>
            <div class="span5">
                <div class='widgetbox'>
                    <h4 class='widgettitle'><?php echo $language->lang_echo('PROFILE_PICTURE'); ?></h4>

                    <div class='widgetcontent' style="text-align:center;">

                        <img src='<?php echo $this->get('profilePic') ?>'  class='profileImg' alt='Profile Picture' id="previousImage"/>
                        <div id="profileImg">
                        </div>

                        <div class="par">

                            <label><?php echo $language->lang_echo('UPLOAD_NEW') ?></label>

                            <div class='fileupload fileupload-new' data-provides='fileupload'>
                                <input type="hidden"/>
                                <div class="input-append">
                                    <div class="uneditable-input span3">
                                        <i class="iconfa-file fileupload-exists"></i>
                                        <span class="fileupload-preview"></span>
                                    </div>
                                    <span class="btn btn-file">
                                        <span class="fileupload-new">Select file</span>
                                        <span class='fileupload-exists'>Change</span>
                                        <input type='file' name='file' onchange="leantime.usersController.readURL(this)"/>
                                    </span>

                                    <a href='#' class='btn fileupload-exists' data-dismiss='fileupload' onclick="leantime.usersController.clearCroppie()">Remove</a>
                                </div>
                                <p class='stdformbutton'>
                                    <span id="save-picture" class="btn btn-primary fileupload-exists ld-ext-right">
                                        <span onclick="leantime.usersController.saveCroppie()">Save Picture</span>
                                        <span class="ld ld-ring ld-spin"</span>
                                    </span>
                                    <input id="picSubmit" type="submit" name="savePic" class="hidden"
                                           value="<?php echo $language->lang_echo('UPLOAD'); ?>"/>
                                </p>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>





