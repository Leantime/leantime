<?php
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$companySettings = $tpl->get('companySettings');
?>

<div class="pageheader">

    <div class="pageicon"><span class="fa fa-cogs"></span></div>
    <div class="pagetitle">
        <h5><?= $tpl->__('label.administration')?></h5>
        <h1><?= $tpl->__('headlines.company_settings')?></h1>
    </div>
</div>

<div class="maincontent">
    <?php echo $tpl->displayNotification(); ?>
    <div class="maincontentinner">
        <div class="row">
            <div class="col-md-12">

                <div class="tabbedwidget tab-primary companyTabs">

                    <ul>
                        <li><a href="#details"><span class="fa fa-building"></span> <?php echo $tpl->__('tabs.details'); ?></a></li>
                        <li><a href="#apiKeys"><i class="fa-solid fa-key"></i> <?php echo $tpl->__('tabs.apiKeys'); ?></a></li>
                        <?php $tpl->dispatchTplEvent('tabs') ?>
                    </ul>


                    <div id="details">


                        <div class="row">
                            <div class="col-md-8">
                                <form class="" method="post" id="" action="<?= BASE_URL ?>/setting/editCompanySettings#details" >
                                    <p><?= $tpl->__('text.these_are_system_wide_settings')?></p>
                                    <br />
                                    <input type="hidden" value="1" name="saveSettings" />

                                    <h4 class="widgettitle title-light"><span
                                            class="fa fa-building"></span><?php echo $tpl->__('subtitles.companydetails'); ?>
                                    </h4>
                                    <div class="row">
                                        <div class="col-md-2">
                                            <label><?= $tpl->__('label.language')?></label>
                                        </div>
                                        <div class="col-md-8">
                                            <select name="language" id="language">
                                                <?php foreach ($tpl->get('languageList') as $languagKey => $languageValue) {?>
                                                    <option
                                                        value="<?= $languagKey?>"
                                                        <?php if ($companySettings['language'] == $languagKey) {
                                                            echo "selected='selected'";
                                                        } ?>><?= $languageValue?></option>
                                                <?php } ?>
                                            </select>


                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-2">
                                            <label><?= $tpl->__('label.company_name')?></label>
                                        </div>
                                        <div class="col-md-8">
                                            <input type="text" name="name" id="companyName"  value="<?php echo $companySettings['name']; ?>" class="pull-left"/>
                                            <small><?= $tpl->__('text.company_name_helper')?></small>
                                        </div>
                                    </div>
                                    <br />
                                    <h4 class="widgettitle title-light"><span
                                            class="fa fa-cog"></span><?php echo $tpl->__('subtitles.defaults'); ?>
                                    </h4>
                                    <div class="row">
                                        <div class="col-md-2">
                                            <label for="messageFrequency"><?php echo $tpl->__('label.messages_frequency') ?></label>
                                        </div>
                                        <div class="col-md-8">
                                                            <span class='field'>
                                                                <select name="messageFrequency" class="input" id="messageFrequency" style="width: 220px">
                                                                    <option value="">--<?php echo $tpl->__('label.choose_option') ?>--</option>
                                                                    <option
                                                                        value="300"
                                                                        <?php if ($companySettings['messageFrequency'] == '300') {
                                                                            echo ' selected ';
                                                                        } ?>><?php echo $tpl->__('label.5min') ?></option>
                                                                    <option
                                                                        value="900"
                                                                        <?php if ($companySettings['messageFrequency'] == '900') {
                                                                            echo ' selected ';
                                                                        } ?>><?php echo $tpl->__('label.15min') ?></option>
                                                                    <option
                                                                        value="1800"
                                                                        <?php if ($companySettings['messageFrequency'] == '1800') {
                                                                            echo ' selected ';
                                                                        } ?>><?php echo $tpl->__('label.30min') ?></option>
                                                                    <option
                                                                        value="3600"
                                                                        <?php if ($companySettings['messageFrequency'] == '3600') {
                                                                            echo ' selected ';
                                                                        } ?>><?php echo $tpl->__('label.1h') ?></option>
                                                                    <option
                                                                        value="10800"
                                                                        <?php if ($companySettings['messageFrequency'] == '10800') {
                                                                            echo ' selected ';
                                                                        } ?>><?php echo $tpl->__('label.3h') ?></option>
                                                                    <option
                                                                        value="36000"
                                                                        <?php if ($companySettings['messageFrequency'] == '36000') {
                                                                            echo ' selected ';
                                                                        } ?>><?php echo $tpl->__('label.6h') ?></option>
                                                                    <option
                                                                        value="43200"
                                                                        <?php if ($companySettings['messageFrequency'] == '43200') {
                                                                            echo ' selected ';
                                                                        } ?>><?php echo $tpl->__('label.12h') ?></option>
                                                                    <option
                                                                        value="86400"
                                                                        <?php if ($companySettings['messageFrequency'] == '86400') {
                                                                            echo ' selected ';
                                                                        } ?>><?php echo $tpl->__('label.24h') ?></option>
                                                                    <option
                                                                        value="172800"
                                                                        <?php if ($companySettings['messageFrequency'] == '172800') {
                                                                            echo ' selected ';
                                                                        } ?>><?php echo $tpl->__('label.48h') ?></option>
                                                                    <option
                                                                        value="604800"
                                                                        <?php if ($companySettings['messageFrequency'] == '604800') {
                                                                            echo ' selected ';
                                                                        } ?>><?php echo $tpl->__('label.1w') ?></option>
                                                                </select> <br/>
                                                            </span>
                                        </div>
                                    </div>
                                    <br />
                                    <h4 class="widgettitle title-light"><span
                                            class="fa fa-bell"></span><?php echo $tpl->__('label.default_notification_types'); ?>
                                    </h4>
                                    <p><?php echo $tpl->__('label.default_notification_types_description'); ?></p>
                                    <div class="row">
                                        <div class="col-md-8">
                                            <?php
                                            $categoryLabels = [
                                                'tasks' => 'label.notification_category_tasks',
                                                'comments' => 'label.notification_category_comments',
                                                'goals' => 'label.notification_category_goals',
                                                'ideas' => 'label.notification_category_ideas',
                                                'projects' => 'label.notification_category_projects',
                                                'boards' => 'label.notification_category_boards',
                                            ];
$defaultNotificationTypes = $tpl->get('defaultNotificationTypes');
$notificationCategories = $tpl->get('notificationCategories');
foreach ($notificationCategories as $categoryKey => $config) { ?>
                                                <div class="form-group">
                                                    <label style="display:flex; align-items:flex-start; gap:8px; cursor:pointer; padding:4px 0;">
                                                        <input type="checkbox"
                                                               name="defaultNotificationEventTypes[]"
                                                               value="<?= $categoryKey ?>"
                                                               style="margin-top:3px;"
                                                               <?php if (in_array($categoryKey, $defaultNotificationTypes)) {
                                                                   echo 'checked="checked"';
                                                               } ?>
                                                        />
                                                        <span>
                                                            <strong><?= $tpl->__($categoryLabels[$categoryKey] ?? $categoryKey) ?></strong><br />
                                                            <small style="color:#888;"><?= $tpl->__($config['description'] ?? '') ?></small>
                                                        </span>
                                                    </label>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>

                                    <br />
                                    <h4 class="widgettitle title-light"><span
                                            class="fa fa-sliders"></span><?php echo $tpl->__('label.default_notification_relevance'); ?>
                                    </h4>
                                    <p><?php echo $tpl->__('label.default_notification_relevance_description'); ?></p>
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <select name="defaultNotificationRelevance" class="form-control" style="max-width:300px;">
                                                    <?php
                                                    $defaultRelevance = $tpl->get('defaultRelevance');
$relevanceLevels = $tpl->get('relevanceLevels');
foreach ($relevanceLevels as $level => $labelKey) { ?>
                                                        <option value="<?= $level ?>" <?= $defaultRelevance === $level ? 'selected' : '' ?>>
                                                            <?= $tpl->__($labelKey) ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="submit" value="<?= $tpl->__('buttons.save')?>" id="saveBtn"/>
                                </form>
                            </div>
                            <div class="col-md-4">

                                <form class="" method="post" id="" action="<?= BASE_URL ?>/setting/editCompanySettings" >
                                    <input type="hidden" value="1" name="saveLogo" />
                                    <h5 class="widgettitle title-light"><?= $tpl->__('headlines.logo')?></h5>
                                    <br />

                                    <div class="row">

                                        <div class="col-md-12">
                                            <?php if ($companySettings['logo'] != '') { ?>
                                            <img src='<?php echo $companySettings['logo'] ?>'  class='logoImg' alt='Logo' id="previousImage" width="260"/>
                                            <?php } else { ?>
                                                <?= $tpl->__('text.no_logo') ?>
                                            <?php } ?>
                                            <div id="logoImg" style="height:auto;">
                                            </div>
                                            <br />
                                            <div class="par">

                                                <label><?= $tpl->__('label.upload_new_logo')?></label>

                                                <div class='fileupload fileupload-new' data-provides='fileupload'>
                                                    <input type="hidden"/>
                                                    <div class="input-append">
                                                        <div class="uneditable-input span3">
                                                            <i class="fa-file fileupload-exists"></i>
                                                            <span class="fileupload-preview"></span>
                                                        </div>
                                                        <span class="btn btn-default btn-file">
                                                            <span class="fileupload-new"><?= $tpl->__('buttons.select_file')?></span>
                                                            <span class='fileupload-exists'><?= $tpl->__('buttons.change')?></span>
                                                            <input type='file' name='file' onchange="leantime.settingController.readURL(this)" />
                                                        </span>

                                                        <a href='#' style="margin-left:5px;" class='btn btn-default fileupload-exists' data-dismiss='fileupload' onclick="leantime.usersController.clearCroppie()"><?= $tpl->__('buttons.remove')?></a>
                                                    </div>
                                                    <p class='stdformbutton'>
                                                        <span id="save-logo" class="btn btn-primary fileupload-exists ld-ext-right">
                                                            <span onclick="leantime.settingController.saveCroppie()"><?= $tpl->__('buttons.save')?></span>
                                                            <span class="ld ld-ring ld-spin"> </span>
                                                        </span>

                                                        <input id="picSubmit" type="submit" name="savePic" class="hidden" value="<?= $tpl->__('buttons.upload')?>" />
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <hr />
                                <?= $tpl->__('text.logo_reset')?><br /><br />
                                <a href="<?= BASE_URL ?>/setting/editCompanySettings?resetLogo=1" class="btn btn-default"><?= $tpl->__('buttons.reset_logo')?></a>
                            </div>
                        </div>
                </div>

                    <div id="apiKeys">
                        <a href="#/api/newApiKey" class="btn btn-primary">Generate API Key</a>
                        <br /> <br />
                        <ul class="sortableTicketList">


                        <?php foreach ($tpl->get('apiKeys') as $apiKey) { ?>
                            <li>
                                <div class="ticketBox">
                                      <div class="inlineDropDownContainer">
                                        <a href="javascript:void(0)" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                                            <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                        </a>
                                        <ul class="dropdown-menu">

                                            <li><a href="#/api/apiKey/<?= $apiKey['id'] ?>"><i class="fa fa-edit"></i> Edit Key</a></li>
                                            <li><a href="<?= BASE_URL?>/api/delAPIKey/<?= $apiKey['id'] ?>" class="delete"><i class="fa fa-trash"></i> Delete Key</a></li>
                                        </ul>
                                    </div>
                                    <a href="#/api/apiKey/<?= $apiKey['id'] ?>"><strong><?= $tpl->escape($apiKey['firstname']) ?></strong></a><br />
                                    lt_<?= $apiKey['username'] ?>***
                                    | <?= $tpl->__('labels.created_on')?>: <?= format($apiKey['createdOn'])->date() ?> | <?= $tpl->__('labels.last_used')?>: <?= format($apiKey['lastlogin'])->date() ?>

                                </div>
                            </li>
                        <?php } ?>
                        </ul>

                    </div>

                    <?php $tpl->dispatchTplEvent('tabsContent') ?>

            </div>
        </div>
    </div>
</div>




</div>


<script>
    jQuery(document).ready(function() {
        jQuery(".companyTabs").tabs({
            activate: function (event, ui) {

                window.location.hash = ui.newPanel.selector;
            }
        });
    });
</script>
