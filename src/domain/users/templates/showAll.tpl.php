<?php
defined('RESTRICTED') or die('Restricted access');
$roles = $this->get('roles');
?>

<div class="pageheader">

    <div class="pageicon"><span class="<?php echo $this->getModulePicture() ?>"></span></div>
    <div class="pagetitle">
        <h5><?php echo $this->__('label.administration') ?></h5>
        <h1><h1><?php echo $this->__('headlines.users'); ?></h1></h1>
    </div>
</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">

        <?php echo $this->displayNotification() ?>

        <div class="row">
            <div class="col-md-6">
                <?php echo $this->displayLink('users.newUser', "<i class='iconfa-plus'></i> ".$this->__('buttons.add_user'), null, array('class' => 'btn btn-primary btn-rounded')) ?>

            </div>
            <div class="col-md-6 align-right">

            </div>
        </div>

        <table class="table table-bordered" cellpadding="0" cellspacing="0" border="0" id="allUsersTable">
            <colgroup>
                <col class="con1">
                <col class="con0">
                <col class="con1">
                <col class="con0">
                <col class="con1">
                <col class="con0">
            </colgroup>
            <thead>
                <tr>
                    <th class='head1'><?php echo $this->__('label.name'); ?></th>
                    <th class='head0'><?php echo $this->__('label.email'); ?></th>
                    <th class='head1'><?php echo $this->__('label.client'); ?></th>
                    <th class='head1'><?php echo $this->__('label.role'); ?></th>
                    <th class='head1'><?php echo $this->__('headlines.twoFA'); ?></th>
                    <th class='head0 no-sort'></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($this->get('allUsers') as $row): ?>
                    <tr>
                        <td style="padding:6px 10px;">
                        <?php echo $this->displayLink('users.editUser', sprintf( $this->__("text.full_name"), $this->escape($row["firstname"]), $this->escape($row["lastname"])), array('id' => $row['id'])); ?>
                        </td>
                        <td><?php echo $row['username']; ?></td>
                        <td><?=$row['clientName']; ?></td>
                        <td><?=$this->__("label.roles.".$roles[$row['role']]); ?></td>
                        <td><?php if($row['twoFAEnabled']){ echo $this->__('label.yes'); }else{ echo $this->__('label.no'); } ?></td>
                        <td><a href="<?=BASE_URL ?>/users/delUser/<?php echo $row['id']?>" class="delete"><i class="fa fa-trash"></i> <?=$this->__('links.delete');?></a></td>
                    </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function() {
            leantime.usersController.initUserTable();
        }
    );

</script>
