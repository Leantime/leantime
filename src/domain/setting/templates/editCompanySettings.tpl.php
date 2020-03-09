<?php
$companySettings= $this->get('companySettings');
?>


<div class="pageheader">

    <div class="pageicon"><span class="fa fa-cogs"></span></div>
    <div class="pagetitle">
        <h5>Administration</h5>
        <h1>Company Settings</h1>
    </div>
</div>

<div class="maincontent">
    <div class="maincontentinner">

        <?php echo $this->displayNotification(); ?>


        <div class="row">
            <div class="col-lg-12">
                <div class="row">
                    <div class="col-md-12">


                        <div id="subscriptionUpdate">
                            <h4 class="widgettitle title-primary">Company Settings</h4>
                            <div class="widgetcontent">

                                <p>These are system wide settings that will affect the look & feel of your leantime experience.</p>
                                <form class="" method="post" id="" action="<?=BASE_URL ?>/setting/editCompanySettings" >
                                    <input type="hidden" value="1" name="saveSettings" />

                                    <div class="row">
                                        <div class="col-md-2">
                                            <label>Company Name</label>
                                        </div>
                                        <div class="col-md-8">
                                            <input type="text" name="name" id="companyName"  value="<?php echo $companySettings['name']; ?>"/>
                                            <small>Appears in the page title and in emails</small>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-2">
                                            <label>Theme Color</label>
                                        </div>
                                        <div class="col-md-8">
                                            <input type="text" id="colorpicker" name="color" class="form-control input-sm" value="<?php echo $companySettings['color']; ?>"/>
                                            <span id="colorSelector" class="colorselector"><span></span></span>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-2">
                                            <label>Logo</label>
                                        </div>
                                        <div class="col-md-4">
                                            <img src='<?php echo $companySettings['logo'] ?>'  class='logoImg' alt='Logo' id="previousImage" width="260"/>
                                            <div id="logoImg" style="height:auto;">
                                            </div>

                                            <div class="par">

                                                <label>Upload a new logo (260px x 60px)</label>

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
                                                        <input type='file' name='file' onchange="leantime.settingController.readURL(this)" />
                                                    </span>

                                                        <a href='#' class='btn fileupload-exists' data-dismiss='fileupload' onclick="leantime.usersController.clearCroppie()">Remove</a>
                                                    </div>
                                                    <p class='stdformbutton'>
                                                        <span id="save-logo" class="btn btn-primary fileupload-exists ld-ext-right">
                                                            <span onclick="leantime.settingController.saveCroppie()">Save Picture</span>
                                                            <span class="ld ld-ring ld-spin"> </span>
                                                        </span>

                                                        <input id="picSubmit" type="submit" name="savePic" class="hidden" value="Upload" />
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <input type="submit" value="Save Settings" id="saveBtn"/>

                                </form>


                            </div>
                        </div>
                    </div>
                </div>
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
