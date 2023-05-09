<?php
$companySettings = $this->get('companySettings');
?>

<div class="pageheader">

    <div class="pageicon"><span class="fa fa-cogs"></span></div>
    <div class="pagetitle">
        <h5><?=$this->__("label.administration")?></h5>
        <h1><?=$this->__("headlines.company_settings")?></h1>
    </div>
</div>

<div class="maincontent">
    <?php echo $this->displayNotification(); ?>
    <div class="maincontentinner">
        <div class="row">
            <div class="col-md-12">

                <div class="tabbedwidget tab-primary companyTabs">

                    <ul>
                        <li><a href="#details"><span class="fa fa-building"></span> <?php echo $this->__('tabs.details'); ?></a></li>
                        <li><a href="#look"><span class="fa fa-group"></span> <?php echo $this->__('tabs.look'); ?></a></li>

                        <li><a href="#apiKeys"><i class="fa-solid fa-key"></i> <?php echo $this->__('tabs.apiKeys'); ?></a></li>
                    </ul>


                    <div id="details">
                        <h5 class="subtitle"><?=$this->__("headlines.company_settings")?></h5>

                        <form class="" method="post" id="" action="<?=BASE_URL ?>/setting/editCompanySettings#details" >
                            <br />
                            <p><?=$this->__("text.these_are_system_wide_settings")?></p>
                            <br />
                            <input type="hidden" value="1" name="saveSettings" />

                            <h4 class="widgettitle title-light"><span
                                    class="fa fa-building"></span><?php echo $this->__('subtitles.companydetails'); ?>
                            </h4>
                            <div class="row">
                                <div class="col-md-2">
                                    <label><?=$this->__("label.language")?></label>
                                </div>
                                <div class="col-md-8">
                                    <select name="language" id="language">
                                        <?php foreach ($this->get("languageList") as $languagKey => $languageValue) {?>
                                            <option value="<?=$languagKey?>" <?php if ($companySettings['language'] == $languagKey) {
                                                echo "selected='selected'";
                                            } ?>><?=$languageValue?></option>
                                        <?php } ?>
                                    </select>


                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-2">
                                    <label><?=$this->__("label.company_name")?></label>
                                </div>
                                <div class="col-md-8">
                                    <input type="text" name="name" id="companyName"  value="<?php echo $companySettings['name']; ?>" class="pull-left"/>
                                    <small><?=$this->__("text.company_name_helper")?></small>
                                </div>
                            </div>
                            <br />

                            <div class="row">
                                <div class="col-md-2">
                                    <label><?=$this->__("label.send_telemetry")?></label>
                                </div>
                                <div class="col-md-8">
                                    <input type="checkbox" name="telemetryActive" id="telemetryActive"  <?=$companySettings['telemetryActive'] == true ? 'checked="checked"' : '';?> />

                                    <i class="fa fa-question-circle" style="vertical-align: bottom;" data-tippy-content="<?=strip_tags($this->__("label.telemetry_background")) ?>"></i>
                                    <div class="clearall"></div><br />
                                </div>
                            </div>
                            <h4 class="widgettitle title-light"><span
                                    class="fa fa-cog"></span><?php echo $this->__('subtitles.defaults'); ?>
                            </h4>
                            <div class="row">
                                <div class="col-md-2">
                                    <label for="messageFrequency"><?php echo $this->__('label.messages_frequency') ?></label>
                                </div>
                                <div class="col-md-8">
                                                    <span class='field'>
                                                        <select name="messageFrequency" class="input" id="messageFrequency" style="width: 220px">
                                                            <option value="">--<?php echo $this->__('label.choose_option') ?>--</option>
                                                            <option value="300" <?php if ($companySettings['messageFrequency'] == "300") {
                                                                echo " selected ";
                                                            } ?>><?php echo $this->__('label.5min') ?></option>
                                                            <option value="900" <?php if ($companySettings['messageFrequency'] == "900") {
                                                                echo " selected ";
                                                            } ?>><?php echo $this->__('label.15min') ?></option>
                                                            <option value="1800" <?php if ($companySettings['messageFrequency'] == "1800") {
                                                                echo " selected ";
                                                            } ?>><?php echo $this->__('label.30min') ?></option>
                                                            <option value="3600" <?php if ($companySettings['messageFrequency'] == "3600") {
                                                                echo " selected ";
                                                            } ?>><?php echo $this->__('label.1h') ?></option>
                                                            <option value="10800" <?php if ($companySettings['messageFrequency'] == "10800") {
                                                                echo " selected ";
                                                            } ?>><?php echo $this->__('label.3h') ?></option>
                                                            <option value="36000" <?php if ($companySettings['messageFrequency'] == "36000") {
                                                                echo " selected ";
                                                            } ?>><?php echo $this->__('label.6h') ?></option>
                                                            <option value="43200" <?php if ($companySettings['messageFrequency'] == "43200") {
                                                                echo " selected ";
                                                            } ?>><?php echo $this->__('label.12h') ?></option>
                                                            <option value="86400" <?php if ($companySettings['messageFrequency'] == "86400") {
                                                                echo " selected ";
                                                            } ?>><?php echo $this->__('label.24h') ?></option>
                                                            <option value="172800" <?php if ($companySettings['messageFrequency'] == "172800") {
                                                                echo " selected ";
                                                            } ?>><?php echo $this->__('label.48h') ?></option>
                                                            <option value="604800" <?php if ($companySettings['messageFrequency'] == "604800") {
                                                                echo " selected ";
                                                            } ?>><?php echo $this->__('label.1w') ?></option>
                                                        </select> <br/>
                                                    </span>
                                </div>
                            </div>
                            <input type="submit" value="<?=$this->__("buttons.save")?>" id="saveBtn"/>
                        </form>

                </div>

                    <div id="look">
                            <div class="row">
                            <div class="col-md-8">
                                <form class="" method="post" id="" action="<?=BASE_URL ?>/setting/editCompanySettings#look" >

                                    <input type="hidden" value="1" name="saveSettings" />
                                    <h5 class="subtitle"><?=$this->__("headlines.colors")?></h5>
                                    <br />
                                    <div class="row">
                                        <div class="col-md-2">
                                            <label><?=$this->__("label.primary_color")?></label>
                                        </div>
                                        <div class="col-md-8">
                                            <input type="color"  name="primarycolor" class="form-control input-sm" value="<?php echo $companySettings['primarycolor']; ?>"/>

                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-2">
                                            <label><?=$this->__("label.secondary_color")?></label>
                                        </div>
                                        <div class="col-md-8">
                                            <input type="color" name="secondarycolor" class="form-control input-sm" value="<?php echo $companySettings['secondarycolor']; ?>"/>

                                        </div>
                                    </div>

                                    <input type="submit" value="<?=$this->__("buttons.save")?>" id="saveBtn"/>
                                </form>
                            </div>
                            <div class="col-md-4">

                                <form class="" method="post" id="" action="<?=BASE_URL ?>/setting/editCompanySettings" >
                                    <input type="hidden" value="1" name="saveLogo" />
                                    <h5 class="subtitle"><?=$this->__("headlines.logo")?></h5>
                                    <br />

                                    <div class="row">

                                        <div class="col-md-12">
                                            <img src='<?php echo $companySettings['logo'] ?>'  class='logoImg' alt='Logo' id="previousImage" width="260"/>
                                            <div id="logoImg" style="height:auto;">
                                            </div>
                                            <br />
                                            <div class="par">

                                                <label><?=$this->__("label.upload_new_logo")?></label>

                                                <div class='fileupload fileupload-new' data-provides='fileupload'>
                                                    <input type="hidden"/>
                                                    <div class="input-append">
                                                        <div class="uneditable-input span3">
                                                            <i class="fa-file fileupload-exists"></i>
                                                            <span class="fileupload-preview"></span>
                                                        </div>
                                                        <span class="btn btn-default btn-file">
                                                            <span class="fileupload-new"><?=$this->__("buttons.select_file")?></span>
                                                            <span class='fileupload-exists'><?=$this->__("buttons.change")?></span>
                                                            <input type='file' name='file' onchange="leantime.settingController.readURL(this)" />
                                                        </span>

                                                        <a href='#' style="margin-left:5px;" class='btn btn-default fileupload-exists' data-dismiss='fileupload' onclick="leantime.usersController.clearCroppie()"><?=$this->__("buttons.remove")?></a>
                                                    </div>
                                                    <p class='stdformbutton'>
                                                        <span id="save-logo" class="btn btn-primary fileupload-exists ld-ext-right">
                                                            <span onclick="leantime.settingController.saveCroppie()"><?=$this->__("buttons.save")?></span>
                                                            <span class="ld ld-ring ld-spin"> </span>
                                                        </span>

                                                        <input id="picSubmit" type="submit" name="savePic" class="hidden" value="<?=$this->__("buttons.upload")?>" />
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <hr />
                                <?=$this->__("text.logo_reset")?><br /><br />
                                <a href="<?=CURRENT_URL ?>&resetLogo=1" class="btn btn-default"><?=$this->__("buttons.reset_logo")?></a>
                            </div>
                        </div>


                    </div>

                    <div id="apiKeys">
                        <a href="<?=BASE_URL?>/setting/editCompanySettings/#/api/newApiKey" class="btn btn-primary">Generate API Key</a>
                        <br /> <br />
                        <ul class="sortableTicketList">


                        <?php foreach($this->get('apiKeys') as $apiKey) { ?>
                            <li>
                                <div class="ticketBox">
                                      <div class="inlineDropDownContainer">
                                        <a href="javascript:void(0)" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                                            <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                        </a>
                                        <ul class="dropdown-menu">

                                            <li><a href="<?=BASE_URL?>/setting/editCompanySettings/#/api/apiKey/<?=$apiKey["id"] ?>"><i class="fa fa-edit"></i> Edit Key</a></li>
                                            <li><a href="<?=BASE_URL?>/api/delAPIKey/<?=$apiKey["id"] ?>" class="delete"><i class="fa fa-trash"></i> Delete Key</a></li>
                                        </ul>
                                    </div>
                                    <a href="<?=BASE_URL?>/setting/editCompanySettings/#/api/apiKey/<?=$apiKey["id"] ?>"><strong><?=$apiKey["firstname"] ?></strong></a><br />
                                    lt_<?=$apiKey["username"] ?>***
                                    | <?=$this->__("labels.created_on")?>: <?=$this->getFormattedDateString($apiKey["createdOn"]) ?> | <?=$this->__("labels.last_used")?>: <?= $this->getFormattedDateString($apiKey["lastlogin"]) ?>

                                </div>
                            </li>
                        <?php } ?>
                        </ul>

                    </div>

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
