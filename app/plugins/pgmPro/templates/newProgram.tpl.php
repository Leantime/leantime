
<?php
 defined('RESTRICTED') or die('Restricted access');
$program = $this->get('program');

?>

<div class="pageheader">

    <div class="pull-right padding-top">

    </div>

    <div class="pageicon"><span class="fa fa-suitcase"></span></div>
    <div class="pagetitle">
        <h5>Program Manager</h5>
        <h1>New Program</h1>
    </div>

</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">

        <?php echo $this->displayNotification(); ?>

        <div class="tabbedwidget tab-primary projectTabs">

            <ul>
                <li><a href="#projectdetails">Program Details</a></li>
            </ul>

            <div id="projectdetails">


                <form action="" method="post" class="stdform">

                    <div class="row-fluid">

                        <div class="span8">
                            <div class="row-fluid">
                                <div class="span12">

                                    <div class="form-group">

                                        <input type="text" name="name" id="name" class="main-title-input" style="width:99%"  value="<?php $this->e($program['name']) ?>" placeholder="Enter the title of your program"/>

                                    </div>


                                    <input type="hidden" name="projectState"  id="projectState" value="0" />


                                </div>
                            </div>
                            <div class="row-fluid">
                                <div class="span12">
                                    <p>
                                        <?php echo $this->__('label.accomplish'); ?>
                                    </p>
                                    <textarea name="details" id="details" class="complexEditor" rows="5" cols="50"><?php echo $program['details'] ?></textarea>

                                </div>
                            </div>
                        </div>
                        <div class="span4">

                            <div class="row-fluid marginBottom">
                                <div class="span12">
                                    <h4 class="widgettitle title-light"><span
                                            class="fa fa-lock-open"></span><?php echo $this->__('labels.defaultaccess'); ?></h4>
                                    Who can access this program?
                                    <br /><br />

                                    <select name="globalProjectUserAccess" style="max-width:300px;">
                                        <option value="restricted" <?=$program['psettings'] == "restricted" ? "selected='selected'" : '' ?>><?php echo $this->__("labels.only_chose"); ?></option>
                                        <option value="clients" <?=$program['psettings'] == "clients" ? "selected='selected'" : ''?>><?php echo $this->__("labels.everyone_in_client"); ?></option>
                                        <option value="all" <?=$program['psettings'] == "all" ? "selected='selected'" : ''?>><?php echo $this->__("labels.everyone_in_org"); ?></option>
                                    </select>

                                </div>
                            </div>


                        </div>

                    </div>
                    <div class="row-fluid padding-top">
                        <?php if ($program['id'] != '') : ?>
                            <div class="pull-right padding-top">
                                <a href="<?=BASE_URL?>/pgmPro/delProgram/<?php echo $program['id']?>" class="delete"><i class="fa fa-trash"></i> <?php echo $this->__('buttons.delete'); ?></a>
                            </div>
                        <?php endif; ?>
                        <input type="submit" name="save" id="save" class="button" value="<?php echo $this->__('buttons.save'); ?>" class="button" />

                    </div>
                </form>


            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function() {
            leantime.projectsController.initProjectTabs();
            leantime.projectsController.initProjectsEditor();

        }
    );

</script>
