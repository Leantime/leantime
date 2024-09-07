@extends($layout)

@section('content')

<?php
$roles = $tpl->get('roles');
?>

<div class="pageheader">

    <div class="pageicon"><span class="fa <?php echo $tpl->getModulePicture() ?>"></span></div>
    <div class="pagetitle">
        <h5><?php echo $tpl->__('label.administration') ?></h5>
        <h1><?php echo $tpl->__('headlines.users'); ?></h1>
    </div>
</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">

        @displayNotification()

        <div class="row">
            <div class="col-md-6">
                <a href="<?=BASE_URL ?>/users/newUser" class="btn btn-primary userEditModal"><i class='fa fa-plus'></i> <?=$tpl->__('buttons.add_user') ?> </a>
            </div>
            <div class="col-md-6 align-right">

            </div>
        </div>

        <table class="table table-bordered" id="allUsersTable">
            <colgroup>
                <col class="con1">
                <col class="con0">
                <col class="con1">
                <col class="con0">
                <col class="con1">
                <col class="con0">
                <col class="con1">
            </colgroup>
            <thead>
                <tr>
                    <th class='head1'><?php echo $tpl->__('label.name'); ?></th>
                    <th class='head0'><?php echo $tpl->__('label.email'); ?></th>
                    <th class='head1'><?php echo $tpl->__('label.client'); ?></th>
                    <th class='head1'><?php echo $tpl->__('label.role'); ?></th>
                    <th class='head1'><?php echo $tpl->__('label.status'); ?></th>
                    <th class='head1'><?php echo $tpl->__('headlines.twoFA'); ?></th>
                    <th class='head0 no-sort'></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($tpl->get('allUsers') as $row) { ?>
                    <tr>
                        <td style="padding:6px 10px;">
                             <a href="<?=BASE_URL ?>/users/editUser/<?=$row['id']?>"><?=sprintf($tpl->__("text.full_name"), $tpl->escape($row["firstname"]), $tpl->escape($row["lastname"])); ?></a>
                        </td>
                        <td><a href="<?=BASE_URL ?>/users/editUser/<?=$row['id']?>"><?=$tpl->escape($row['username']); ?></a></td>
                        <td><?=$tpl->escape($row['clientName']); ?></td>
                        <td><?=$tpl->__("label.roles." . $roles[$row['role']]); ?></td>
                        <td><?php if (strtolower($row['status']) == 'a') {
                            echo $tpl->__('label.active');
                            } elseif (strtolower($row['status']) == 'i') {
                                echo $tpl->__('label.invited');
                            } else {
                                echo $tpl->__('label.deactivated');
                            } ?></td>
                        <td><?php if ($row['twoFAEnabled']) {
                            echo $tpl->__('label.yes');
                            } else {
                                echo $tpl->__('label.no');
                            } ?></td>
                        <td><a href="<?=BASE_URL ?>/users/delUser/<?php echo $row['id']?>" class="delete"><i class="fa fa-trash"></i> <?=$tpl->__('links.delete');?></a></td>
                    </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function() {
            leantime.usersController.initUserTable();
            leantime.usersController._initModals();
            leantime.usersController.initUserEditModal();

        }
    );

</script>
