@extends($layout)

@section('content')

<?php
$values = $tpl->get('values');
?>

<?php $tpl->dispatchTplEvent('beforePageHeaderOpen'); ?>
<div class="pageheader">
    <?php $tpl->dispatchTplEvent('afterPageHeaderOpen'); ?>
    <div class="pageicon"><span class="fa fa-address-book"></span></div>
    <div class="pagetitle">
        <h5><?php echo $tpl->__('label.administration') ?></h5>
        <h1><?php echo $tpl->__('headline.new_client'); ?></h1>
    </div>
    <?php $tpl->dispatchTplEvent('beforePageHeaderClose'); ?>
</div><!--pageheader-->
<?php $tpl->dispatchTplEvent('afterPageHeaderClose'); ?>

<div class="maincontent">
    <div class="maincontentinner">

        <?php echo $tpl->displayNotification() ?>

        <div class="widget">
           <h4 class="widgettitle"><?php echo $tpl->__('subtitle.details'); ?></h4>
           <div class="widgetcontent">

                <form action="" method="post" class="stdform">

                    <?php $tpl->dispatchTplEvent('afterFormOpen'); ?>

                    <div class="row row-fluid">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="span4 control-label"><?php echo $tpl->__('label.name') ?></label>
                                <div class="span6">
                                    <input type="text" name="name" id="name" value="<?php $tpl->e($values['name']); ?>" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="span4 control-label"><?php echo $tpl->__('label.email') ?></label>
                                <div class="span6">
                                    <input type="text" name="email" id="email" value="<?php $tpl->e($values['email']); ?>" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="span4 control-label"><?php echo $tpl->__('label.url') ?></label>
                                <div class="span6">
                                    <input
                                            type="text" name="internet" id="internet"
                                            value="<?php $tpl->e($values['internet']); ?>" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="span4 control-label"><?php echo $tpl->__('label.street') ?></label>
                                <div class="span6">
                                    <input
                                            type="text" name="street" id="street"
                                            value="<?php $tpl->e($values['street']); ?>" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="span4 control-label"><?php echo $tpl->__('label.zip') ?></label>
                                <div class="span6">
                                    <input type="text"
                                           name="zip" id="zip" value="<?php $tpl->e($values['zip']); ?>" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="span4 control-label"><?php echo $tpl->__('label.city') ?></label>
                                <div class="span6">
                                    <input type="text"
                                           name="city" id="city" value="<?php $tpl->e($values['city']); ?>" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="span4 control-label"><?php echo $tpl->__('label.state') ?></label>
                                <div class="span6">
                                    <input
                                            type="text" name="state" id="state"
                                            value="<?php $tpl->e($values['state']); ?>" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="span4 control-label"><?php echo $tpl->__('label.country') ?></label>
                                <div class="span6">
                                    <input
                                            type="text" name="country" id="country"
                                            value="<?php $tpl->e($values['country']); ?>" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="span4 control-label"><?php echo $tpl->__('label.phone') ?></label>
                                <div class="span6">
                                    <input
                                            type="text" name="phone" id="phone"
                                            value="<?php $tpl->e($values['phone']); ?>" />
                                </div>
                            </div>

                            <?php $tpl->dispatchTplEvent('beforeSubmitButton'); ?>

                            <div class="form-group">
                                <div class="span4 control-label">
                                    <input type="submit" name="save" id="save"
                                           value="<?php echo $tpl->__('buttons.save') ?>" class="btn btn-primary" />
                                </div>
                                <div class="span6">

                                </div>
                            </div>
                        </div>
                    </div>

                    <?php $tpl->dispatchTplEvent('beforeFormClose'); ?>

                </form>
            </div>
        </div>
    </div>
</div>
