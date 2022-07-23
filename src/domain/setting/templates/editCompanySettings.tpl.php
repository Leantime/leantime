<?php
$companySettings= $this->get('companySettings');
?>

<div class="pageheader">

    <div class="pageicon"><span class="fa fa-cogs"></span></div>
    <div class="pagetitle">
        <h5><?=$this->__("label.administration")?></h5>
        <h1><?=$this->__("headlines.company_settings")?></h1>
    </div>
</div>

<div class="maincontent">

<div class="row">
    <div class="col-md-8">
        <form class="" method="post" id="" action="<?=BASE_URL ?>/setting/editCompanySettings" >
            <div class="maincontentinner">
                <?php echo $this->displayNotification(); ?>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="row">
                            <div class="col-md-12">

                                <div id="subscriptionUpdate">
                                    <h5 class="subtitle"><?=$this->__("headlines.company_settings")?></h5>

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
                                                    <?php foreach($this->get("languageList") as $languagKey => $languageValue){?>
                                                        <option value="<?=$languagKey?>" <?php if($companySettings['language'] == $languagKey) echo "selected='selected'" ?>><?=$languageValue?></option>
                                                    <?php } ?>
                                                </select>


                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-2">
                                                <label><?=$this->__("label.company_name")?></label>
                                            </div>
                                            <div class="col-md-8">
                                                <input type="text" name="name" id="companyName"  value="<?php echo $companySettings['name']; ?>"/>
                                                <small><?=$this->__("text.company_name_helper")?></small>
                                            </div>
                                        </div>
                                        <br />
                                        <h4 class="widgettitle title-light"><span
                                                    class="fa fa-paint-brush"></span><?php echo $this->__('subtitles.look_and_feel'); ?>
                                        </h4>
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

                                        <div class="row">
                                            <div class="col-md-2">
                                                <label><?=$this->__("label.send_telemetry")?></label>
                                            </div>
                                            <div class="col-md-8">
                                                <input type="checkbox" name="telemetryActive" id="telemetryActive"  <?=$companySettings['telemetryActive']==true?'checked="checked"':'';?> />
                                                <div class="clearall"></div>
                                                <small style="vertical-align: text-top;"><?=$this->__("label.telemetry_background")?></small>
                                            </div>
                                        </div>
                                        <br />
                                        <input type="submit" value="<?=$this->__("buttons.save")?>" id="saveBtn"/>

                                    </form>



                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="col-md-4">

            <div class="maincontentinner">
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
                                        <i class="iconfa-file fileupload-exists"></i>
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

            </div>

    </div>
</div>
</div>
<script>
    // color picker
    if(jQuery('#colorpicker').length > 0) {
        jQuery('#colorSelector span').css('backgroundColor', '#' + jQuery('#colorpicker').val());
        jQuery('#colorSelector').ColorPicker({
            color: jQuery('#colorpicker').val(),
            onShow: function (colpkr) {
                jQuery(colpkr).fadeIn(500);
                return false;
            },
            onHide: function (colpkr) {
                jQuery(colpkr).fadeOut(500);
                return false;
            },
            onChange: function (hsb, hex, rgb) {
                jQuery('#colorSelector span').css('backgroundColor', '#' + hex);
                jQuery('#colorpicker').val(''+hex);

                jQuery(".header .logo, .cr-boundary, .header, .widgettitle.title-primary, .leftmenu .nav-tabs.nav-stacked .dropdown ul li.active a, input[type='submit'], button").css('backgroundColor', '#' + hex);
                jQuery(".widgetcontent, .pageicon").css('borderColor', '#' + hex);
                jQuery(".pagetitle h1, .pageicon").css("color", '#' + hex);

            }
        });
    }

</script>
