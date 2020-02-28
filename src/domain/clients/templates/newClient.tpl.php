<?php
defined('RESTRICTED') or die('Restricted access');
$values = $this->get('values');
?>

<div class="pageheader">


<div class="pageicon"><span class="fa fa-address-book"></span></div>
    <div class="pagetitle">
        <h5><?php echo $this->__('label.administration') ?></h5>
        <h1><?php echo $this->__('headline.new_client'); ?></h1>
    </div>
</div><!--pageheader-->
        
<div class="maincontent">
    <div class="maincontentinner">

        <?php echo $this->displayNotification() ?>

        <div class="widget">
           <h4 class="widgettitle"><?php echo $this->__('subtitle.details'); ?></h4>
           <div class="widgetcontent">

                <form action="" method="post" class="stdform">

                    <div class="row row-fluid">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="span4 control-label"><?php echo $this->__('label.name') ?></label>
                                <div class="span6">
                                    <input type="text" name="name" id="name" value="<?php $this->e($values['name']); ?>" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="span4 control-label"><?php echo $this->__('label.email') ?></label>
                                <div class="span6">
                                    <input type="text" name="email" id="email" value="<?php $this->e($values['email']); ?>" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="span4 control-label"><?php echo $this->__('label.url') ?></label>
                                <div class="span6">
                                    <input
                                            type="text" name="internet" id="internet"
                                            value="<?php $this->e($values['internet']); ?>" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="span4 control-label"><?php echo $this->__('label.street') ?></label>
                                <div class="span6">
                                    <input
                                            type="text" name="street" id="street"
                                            value="<?php $this->e($values['street']); ?>" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="span4 control-label"><?php echo $this->__('label.zip') ?></label>
                                <div class="span6">
                                    <input type="text"
                                           name="zip" id="zip" value="<?php $this->e($values['zip']); ?>" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="span4 control-label"><?php echo $this->__('label.city') ?></label>
                                <div class="span6">
                                    <input type="text"
                                           name="city" id="city" value="<?php $this->e($values['city']); ?>" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="span4 control-label"><?php echo $this->__('label.state') ?></label>
                                <div class="span6">
                                    <input
                                            type="text" name="state" id="state"
                                            value="<?php $this->e($values['state']); ?>" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="span4 control-label"><?php echo $this->__('label.country') ?></label>
                                <div class="span6">
                                    <input
                                            type="text" name="country" id="country"
                                            value="<?php $this->e($values['country']); ?>" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="span4 control-label"><?php echo $this->__('label.phone') ?></label>
                                <div class="span6">
                                    <input
                                            type="text" name="phone" id="phone"
                                            value="<?php $this->e($values['phone']); ?>" />
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="span4 control-label">
                                    <input type="submit" name="save" id="save"
                                           value="<?php echo $this->__('buttons.save') ?>" class="btn btn-primary" />
                                </div>
                                <div class="span6">

                                </div>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
